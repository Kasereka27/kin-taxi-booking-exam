<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Ride;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboardDriver(Request $request): View
    {
        $driver = $request->user();
        $profile = $driver->driverProfile;
        $today = today();

        $revenueToday = (float) $driver->ridesAsDriver()
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->sum('price');

        $ridesToday = $driver->ridesAsDriver()
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->count();

        $completedTotal = $driver->ridesAsDriver()
            ->where('status', 'completed')
            ->count();

        $ratingsCount = Rating::where('to_user_id', $driver->id)->count();

        $days = collect(range(6, 0))->map(fn (int $offset) => $today->copy()->subDays($offset));
        $weekData = $days->map(fn ($day) => [
            'label' => ucfirst($day->translatedFormat('D')),
            'total' => (float) $driver->ridesAsDriver()
                ->where('status', 'completed')
                ->whereDate('completed_at', $day)
                ->sum('price'),
        ]);
        $weekTotal = (float) $weekData->sum('total');
        $weekMax = max($weekData->max('total'), 1);

        $recentRides = $driver->ridesAsDriver()
            ->where('status', 'completed')
            ->with('client')
            ->latest('completed_at')
            ->limit(6)
            ->get();

        $pendingRequests = Ride::with('client')
            ->where('status', 'pending')
            ->whereNull('driver_id')
            ->latest('requested_at')
            ->limit(4)
            ->get();

        return view('pageContent.dashboardDriver', [
            'profile' => $profile,
            'revenueToday' => $revenueToday,
            'ridesToday' => $ridesToday,
            'completedTotal' => $completedTotal,
            'ratingsCount' => $ratingsCount,
            'weekData' => $weekData,
            'weekTotal' => $weekTotal,
            'weekMax' => $weekMax,
            'recentRides' => $recentRides,
            'pendingRequests' => $pendingRequests,
        ]);
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
