<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RideRequest;
use App\Http\Resources\RideResource;
use App\Http\Responses\ApiResponse;
use App\Mail\RideBookedMail;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\NewRideAvailable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RideController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rides = Ride::query()
            ->with(['client', 'driver'])
            ->when($user->isClient(), fn ($query) => $query->where('client_id', $user->id))
            ->when($user->isDriver(), fn ($query) => $query->where('driver_id', $user->id))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($inner) use ($search) {
                    $inner->where('pickup_addr', 'like', "%{$search}%")
                        ->orWhere('dropoff_addr', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return ApiResponse::success([
            'rides' => RideResource::collection($rides),
            'meta' => [
                'current_page' => $rides->currentPage(),
                'last_page' => $rides->lastPage(),
                'per_page' => $rides->perPage(),
                'total' => $rides->total(),
            ],
        ]);
    }

    public function store(RideRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->isClient() && ! $user->isAdmin()) {
            return ApiResponse::error('Seuls les clients peuvent réserver une course.', 403);
        }

        $validated = $request->validated();
        $distance = Ride::distanceKmBetween(
            (float) $validated['pickup_lat'],
            (float) $validated['pickup_lng'],
            (float) $validated['dropoff_lat'],
            (float) $validated['dropoff_lng'],
        );

        $ride = $user->ridesAsClient()->create([
            'pickup_addr' => $validated['pickup_addr'],
            'pickup_lat' => $validated['pickup_lat'],
            'pickup_lng' => $validated['pickup_lng'],
            'dropoff_addr' => $validated['dropoff_addr'],
            'dropoff_lat' => $validated['dropoff_lat'],
            'dropoff_lng' => $validated['dropoff_lng'],
            'vehicle_type' => $validated['vehicle_type'],
            'status' => 'pending',
            'distance_km' => $distance,
            'price' => Ride::estimatePrice($validated['vehicle_type'], $distance),
            'requested_at' => now(),
        ]);

        $onlineDrivers = User::where('role', 'driver')
            ->whereHas('driverProfile', fn ($query) => $query->where('is_online', true))
            ->get();
        Notification::send($onlineDrivers, new NewRideAvailable($ride));
        Mail::to($user)->queue(new RideBookedMail($ride));

        $ride->load(['client', 'driver']);

        return ApiResponse::success([
            'ride' => new RideResource($ride),
        ], 'Course enregistrée.', 201);
    }

    public function show(Request $request, Ride $ride): JsonResponse
    {
        $this->authorize('view', $ride);

        $ride->load(['client', 'driver']);

        return ApiResponse::success([
            'ride' => new RideResource($ride),
        ]);
    }

    public function destroy(Request $request, Ride $ride): JsonResponse
    {
        $this->authorize('delete', $ride);

        $ride->delete();

        return ApiResponse::success(null, 'Course supprimée.');
    }

    public function cancel(Request $request, Ride $ride): JsonResponse
    {
        $this->authorize('update', $ride);

        if ($ride->status === 'cancelled') {
            return ApiResponse::error('Cette course est déjà annulée.', 422);
        }

        $ride->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return ApiResponse::success([
            'ride' => new RideResource($ride->fresh(['client', 'driver'])),
        ], 'Course annulée.');
    }
}
