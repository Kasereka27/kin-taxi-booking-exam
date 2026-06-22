<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboardDriver(): View
    {
        return view('pageContent.dashboardDriver');
    }

    public function dashboardClient(Request $request): View
    {
        $user = $request->user();

        $totalRides = $user->ridesAsClient()->count();

        $monthSpend = $user->payments()
            ->where('status', 'success')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $totalDistance = $user->ridesAsClient()
            ->where('status', 'completed')
            ->sum('distance_km');

        $avgRatingGiven = $user->ridesAsClient()
            ->whereHas('ratings')
            ->withAvg('ratings', 'stars')
            ->get()
            ->avg('ratings_avg_stars');

        $currentRide = $user->ridesAsClient()
            ->with('driver')
            ->whereIn('status', ['pending', 'assigned', 'approche', 'course'])
            ->latest()
            ->first();

        $recentRides = $user->ridesAsClient()
            ->with('driver')
            ->latest()
            ->limit(5)
            ->get();

        return view('pageContent.dashboardClient', [
            'totalRides' => $totalRides,
            'monthSpend' => $monthSpend,
            'totalDistance' => $totalDistance,
            'avgRatingGiven' => $avgRatingGiven,
            'currentRide' => $currentRide,
            'recentRides' => $recentRides,
        ]);
    }
}
