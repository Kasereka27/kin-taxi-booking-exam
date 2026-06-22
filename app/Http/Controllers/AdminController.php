<?php

namespace App\Http\Controllers;

use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $today = today();

        $ridesToday = Ride::whereDate('requested_at', $today)->count();

        $revenueMonth = (float) Payment::where('status', 'success')
            ->whereMonth('paid_at', $today->month)
            ->whereYear('paid_at', $today->year)
            ->sum('amount');

        $onlineDrivers = DriverProfile::where('is_online', true)->count();

        $totalRides = Ride::count();
        $cancelledRides = Ride::where('status', 'cancelled')->count();
        $cancellationRate = $totalRides > 0
            ? round($cancelledRides / $totalRides * 100, 1)
            : 0.0;

        $liveStatuses = ['pending', 'assigned', 'approche', 'course'];
        $liveRides = Ride::with(['client', 'driver'])
            ->whereIn('status', $liveStatuses)
            ->latest('requested_at')
            ->limit(8)
            ->get();

        if ($liveRides->isEmpty()) {
            $liveRides = Ride::with(['client', 'driver'])
                ->latest('requested_at')
                ->limit(8)
                ->get();
        }

        $topDrivers = User::where('role', 'driver')
            ->withCount(['ridesAsDriver as completed_rides_count' => function ($query) {
                $query->where('status', 'completed');
            }])
            ->with('driverProfile')
            ->orderByDesc('completed_rides_count')
            ->limit(5)
            ->get();

        $paymentsByMethod = Payment::where('status', 'success')
            ->selectRaw('method, count(*) as count, sum(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();
        $paymentsCount = (int) $paymentsByMethod->sum('count');

        $days = collect(range(6, 0))->map(fn (int $offset) => $today->copy()->subDays($offset));
        $chartLabels = $days->map(fn ($day) => ucfirst($day->translatedFormat('D d/m')))->all();
        $chartRevenue = $days->map(fn ($day) => (float) Payment::where('status', 'success')
            ->whereDate('paid_at', $day)
            ->sum('amount'))->all();
        $chartRides = $days->map(fn ($day) => Ride::whereDate('requested_at', $day)->count())->all();

        return view('pageContent.dashboardAdmin', [
            'ridesToday' => $ridesToday,
            'revenueMonth' => $revenueMonth,
            'onlineDrivers' => $onlineDrivers,
            'cancellationRate' => $cancellationRate,
            'liveRides' => $liveRides,
            'topDrivers' => $topDrivers,
            'paymentsByMethod' => $paymentsByMethod,
            'paymentsCount' => $paymentsCount,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'chartRides' => $chartRides,
        ]);
    }

    public function users(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');

        $users = User::query()
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('pageContent.adminUsers', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    public function toggleUserActive(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Impossible de modifier le statut d’un administrateur.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $label = $user->is_active ? 'réactivé' : 'bloqué';

        return back()->with('status', "Le compte de {$user->firstname} {$user->lastname} a été {$label}.");
    }
}
