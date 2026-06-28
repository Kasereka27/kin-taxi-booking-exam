<?php

namespace App\Http\Controllers;

use App\Http\Requests\RideRequest;
use App\Mail\RideBookedMail;
use App\Mail\RideStatusMail;
use App\Models\Ride;
use App\Models\User;
use App\Notifications\NewRideAvailable;
use App\Notifications\RideAccepted;
use App\Notifications\RideCancelled;
use App\Services\ActivityLogService;
use App\Services\RideTrackingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class RideController extends Controller
{
    /**
     * Liste paginée des courses de l'utilisateur, avec recherche et filtre par statut.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $rides = Ride::query()
            ->with(['client', 'driver', 'payments' => fn ($query) => $query->where('status', 'success')->latest('paid_at')])
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
            ->paginate(10)
            ->withQueryString();

        return view('pageContent.historique', [
            'rides' => $rides,
            'statusLabels' => Ride::statusLabels(),
            'filters' => [
                'status' => $request->string('status')->toString(),
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function store(RideRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $routePolyline = $request->decodedRoutePolyline();

        $distance = $routePolyline !== null
            ? Ride::routeDistanceKm($routePolyline)
            : Ride::distanceKmBetween(
                (float) $validated['pickup_lat'],
                (float) $validated['pickup_lng'],
                (float) $validated['dropoff_lat'],
                (float) $validated['dropoff_lng'],
            );

        $ride = $request->user()->ridesAsClient()->create([
            'pickup_addr' => $validated['pickup_addr'],
            'pickup_lat' => $validated['pickup_lat'],
            'pickup_lng' => $validated['pickup_lng'],
            'dropoff_addr' => $validated['dropoff_addr'],
            'dropoff_lat' => $validated['dropoff_lat'],
            'dropoff_lng' => $validated['dropoff_lng'],
            'route_polyline' => $routePolyline,
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

        Mail::to($request->user())->queue(new RideBookedMail($ride));

        app(ActivityLogService::class)->log(
            ActivityLogService::ACTION_RIDE_CREATED,
            'Course '.Ride::referenceFor($ride->id)." : {$ride->pickup_addr} → {$ride->dropoff_addr}.",
            $request->user(),
            $request,
        );

        return redirect()
            ->route('user.dashboardClient')
            ->with('success', 'Votre course a été enregistrée. Recherche d\'un chauffeur en cours…');
    }

    public function show(Ride $ride): View
    {
        $this->authorize('view', $ride);

        $ride->load(['client', 'driver', 'payments', 'ratings']);

        return view('pageContent.rideShow', ['ride' => $ride]);
    }

    public function destroy(Ride $ride): RedirectResponse
    {
        $this->authorize('delete', $ride);

        $ride->delete();

        return redirect()
            ->route('rides.index')
            ->with('success', 'La course a été supprimée.');
    }

    /**
     * Un chauffeur accepte une course en attente et se la voit assignée.
     */
    public function accept(Request $request, Ride $ride): RedirectResponse
    {
        $driver = $request->user();

        if (! $driver->isDriver()) {
            abort(403);
        }

        if ($ride->status !== 'pending' || $ride->driver_id !== null) {
            return back()->with('error', 'Cette course n’est plus disponible.');
        }

        $ride->update([
            'driver_id' => $driver->id,
            'status' => 'assigned',
            'accepted_at' => now(),
            'price' => $ride->price ?? Ride::estimatePrice($ride->vehicle_type, (float) ($ride->distance_km ?? 0)),
        ]);

        $ride->loadMissing('client');
        $ride->client?->notify(new RideAccepted($ride));

        if ($ride->client) {
            Mail::to($ride->client)->queue(new RideStatusMail($ride, 'accepted'));
        }

        app(RideTrackingService::class)->initializeTracking($ride);

        app(ActivityLogService::class)->log(
            ActivityLogService::ACTION_RIDE_ACCEPTED,
            'Course '.Ride::referenceFor($ride->id).' acceptée par le chauffeur.',
            $driver,
            $request,
        );

        return back()->with('status', 'Course acceptée. Bonne route !');
    }

    /**
     * Annulation d'une course (opération de mise à jour du statut).
     */
    public function cancel(Request $request, Ride $ride): RedirectResponse
    {
        $this->authorize('update', $ride);

        $ride->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $actor = $request->user();
        $ride->loadMissing(['client', 'driver']);
        $recipients = collect([$ride->client, $ride->driver])
            ->filter()
            ->reject(fn (User $user) => $user->id === $actor->id);
        Notification::send($recipients, new RideCancelled($ride, $actor));

        $ride->refresh();
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->queue(new RideStatusMail($ride, 'cancelled', $actor));
        }

        app(ActivityLogService::class)->log(
            ActivityLogService::ACTION_RIDE_CANCELLED,
            'Course '.Ride::referenceFor($ride->id).' annulée.',
            $actor,
            $request,
        );

        return redirect()
            ->back(fallback: route('rides.index'))
            ->with('success', 'La course a été annulée.');
    }
}
