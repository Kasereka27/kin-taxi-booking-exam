<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('pageContent.home');
    }

    public function reservation(): View
    {
        return view('pageContent.reservation');
    }

    /**
     * Suivi en direct : course active de l'utilisateur connecté, ou vue vide.
     */
    public function suivi(Request $request): View
    {
        $ride = $request->user()
            ? $this->resolveActiveRide($request->user())
            : null;

        return view('pageContent.suivi', [
            'ride' => $ride,
        ]);
    }

    /**
     * Suivi d'une course précise (client, chauffeur ou admin).
     */
    public function trackRide(Request $request, Ride $ride): View
    {
        $this->authorize('view', $ride);

        if (! $ride->isTrackable()) {
            return view('pageContent.suivi', [
                'ride' => null,
                'inactiveRide' => $ride->load(['client', 'driver.driverProfile']),
            ]);
        }

        $ride->load(['client', 'driver.driverProfile']);

        return view('pageContent.suivi', [
            'ride' => $ride,
        ]);
    }

    public function tarifs(): View
    {
        return view('pageContent.tarifs');
    }

    public function about(): View
    {
        return view('pageContent.a-propos');
    }

    public function contact(): View
    {
        return view('pageContent.contact');
    }

    public function cgu(): View
    {
        return view('pageContent.cgu');
    }

    public function privacy(): View
    {
        return view('pageContent.confidentialite');
    }

    /**
     * Retourne la course en cours la plus récente pour un client ou un chauffeur.
     */
    private function resolveActiveRide(User $user): ?Ride
    {
        $query = Ride::query()
            ->with(['client', 'driver.driverProfile'])
            ->whereIn('status', Ride::trackableStatuses())
            ->latest('requested_at');

        if ($user->isClient()) {
            return $query->where('client_id', $user->id)->first();
        }

        if ($user->isDriver()) {
            return $query->where('driver_id', $user->id)->first();
        }

        return null;
    }
}
