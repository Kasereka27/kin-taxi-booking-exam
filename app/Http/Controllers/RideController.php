<?php

namespace App\Http\Controllers;

use App\Http\Requests\RideRequest;
use App\Models\Ride;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RideController extends Controller
{
    /**
     * Liste paginée des courses de l'utilisateur, avec recherche et filtre par statut.
     */
    public function index(Request $request): View
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

        $distance = round(random_int(20, 250) / 10, 2);

        $request->user()->ridesAsClient()->create([
            'pickup_addr' => $validated['pickup_addr'],
            'dropoff_addr' => $validated['dropoff_addr'],
            'vehicle_type' => $validated['vehicle_type'],
            'status' => 'pending',
            'distance_km' => $distance,
            'price' => Ride::estimatePrice($validated['vehicle_type'], $distance),
            'requested_at' => now(),
        ]);

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

        return back()->with('status', 'Course acceptée. Bonne route !');
    }

    /**
     * Annulation d'une course (opération de mise à jour du statut).
     */
    public function cancel(Ride $ride): RedirectResponse
    {
        $this->authorize('update', $ride);

        $ride->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return redirect()
            ->route('rides.index')
            ->with('success', 'La course a été annulée.');
    }
}
