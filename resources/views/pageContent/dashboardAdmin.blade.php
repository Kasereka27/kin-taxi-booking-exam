@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Dashboard Administrateur')

@section('header')
@show

@php
    $statusStyles = [
        'pending' => 'bg-gray-100 text-gray-700',
        'assigned' => 'bg-blue-100 text-blue-700',
        'approche' => 'bg-blue-100 text-blue-700',
        'course' => 'bg-yellow-100 text-yellow-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];
    $methodLabels = [
        'mpesa' => 'M-Pesa',
        'airtel' => 'Airtel Money',
        'orange' => 'Orange Money',
        'cash' => 'Espèces',
        'card' => 'Carte bancaire',
    ];
@endphp

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    <aside class="bg-ink text-gray-300 p-5 flex lg:flex-col gap-1 overflow-x-auto">
      @include('partials.brand-logo', ['class' => 'hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2'])
      <nav class="flex lg:flex-col gap-1 flex-1">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap bg-taxi text-ink">📊 <span class="hidden lg:inline">Vue d'ensemble</span></a>
        <a href="#courses" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🚕 <span class="hidden lg:inline">Courses</span></a>
        <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">👥 <span class="hidden lg:inline">Utilisateurs</span></a>
        <a href="{{ route('admin.users', ['role' => 'driver']) }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🧑‍✈️ <span class="hidden lg:inline">Chauffeurs</span></a>
        <a href="#paiements" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">💰 <span class="hidden lg:inline">Paiements</span></a>
      </nav>
      <div class="hidden lg:block border-t border-gray-800 pt-4 mt-4">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition">🚪 Déconnexion</button>
        </form>
      </div>
    </aside>

    <main class="p-7 lg:px-9">
      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div><h1 class="text-2xl font-extrabold">Administration</h1><p class="text-gray-500">Pilotage de la plateforme en temps réel.</p></div>
        <div class="flex gap-3 items-center"><span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700"><span class="w-2 h-2 rounded-full bg-green-500"></span> Système opérationnel</span><div class="w-10 h-10 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">{{ strtoupper(substr(auth()->user()->firstname, 0, 1).substr(auth()->user()->lastname, 0, 1)) }}</div></div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Courses aujourd'hui</div><div class="text-3xl font-extrabold mt-1">{{ number_format($ridesToday, 0, ',', ' ') }}</div><div class="text-gray-500 text-sm mt-1.5">demandées le {{ now()->translatedFormat('d M') }}</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Chiffre d'affaires (mois)</div><div class="text-3xl font-extrabold mt-1">@fc($revenueMonth)</div><div class="text-gray-500 text-sm mt-1.5">{{ now()->translatedFormat('F Y') }}</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Chauffeurs en ligne</div><div class="text-3xl font-extrabold mt-1">{{ number_format($onlineDrivers, 0, ',', ' ') }}</div><div class="text-gray-500 text-sm mt-1.5">connectés</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Taux d'annulation</div><div class="text-3xl font-extrabold mt-1">{{ number_format($cancellationRate, 1, ',', ' ') }}%</div><div class="text-gray-500 text-sm mt-1.5">sur l'ensemble des courses</div></div>
      </div>

      <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 mb-6">
        <h3 class="text-lg font-bold mb-4">Activité des 7 derniers jours</h3>
        <div class="relative h-72 w-full">
          <canvas id="adminActivityChart"></canvas>
        </div>
      </div>

      <div id="courses" class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">Courses en cours</h2><span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700"><span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Live</span></div>
      <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
        <table class="w-full bg-white">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3.5 font-bold">Réf.</th><th class="text-left px-4 py-3.5 font-bold">Client</th><th class="text-left px-4 py-3.5 font-bold">Chauffeur</th><th class="text-left px-4 py-3.5 font-bold">Trajet</th><th class="text-left px-4 py-3.5 font-bold">Montant</th><th class="text-left px-4 py-3.5 font-bold">Statut</th><th class="text-left px-4 py-3.5 font-bold">Action</th></tr></thead>
          <tbody>
            @forelse ($liveRides as $ride)
            <tr class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-4 py-3.5"><strong>{{ $ride->reference() }}</strong></td>
              <td class="px-4 py-3.5">{{ $ride->client?->firstname }} {{ Str::limit($ride->client?->lastname, 1, '.') }}</td>
              <td class="px-4 py-3.5">{{ $ride->driver ? $ride->driver->firstname.' '.Str::limit($ride->driver->lastname, 1, '.') : '—' }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ Str::before($ride->pickup_addr, ',') }} → {{ Str::before($ride->dropoff_addr, ',') }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap">@fc($ride->price)</td>
              <td class="px-4 py-3.5"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles[$ride->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $ride->statusLabel() }}</span></td>
              <td class="px-4 py-3.5"><a href="{{ route('rides.show', $ride) }}" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold border-2 border-gray-300 hover:border-ink transition">Voir</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune course pour le moment.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="grid lg:grid-cols-2 gap-6">
        <div id="chauffeurs" class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Top chauffeurs</h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3 font-bold">Chauffeur</th><th class="text-left px-4 py-3 font-bold">Courses</th><th class="text-left px-4 py-3 font-bold">Note</th><th class="text-left px-4 py-3 font-bold">Statut</th></tr></thead>
              <tbody>
                @forelse ($topDrivers as $driver)
                <tr class="border-t border-gray-200">
                  <td class="px-4 py-3">{{ $driver->firstname }} {{ $driver->lastname }}</td>
                  <td class="px-4 py-3">{{ number_format($driver->completed_rides_count, 0, ',', ' ') }}</td>
                  <td class="px-4 py-3">{{ $driver->driverProfile?->rating ? number_format((float) $driver->driverProfile->rating, 2).'★' : '—' }}</td>
                  <td class="px-4 py-3">
                    @if ($driver->driverProfile?->is_online)
                      <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">En ligne</span>
                    @else
                      <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">Hors ligne</span>
                    @endif
                  </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucun chauffeur.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
        <div id="paiements" class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Répartition des paiements</h3>
          @forelse ($paymentsByMethod as $method)
            @php $percent = $paymentsCount > 0 ? round($method->count / $paymentsCount * 100) : 0; @endphp
            <div class="mb-4">
              <div class="flex justify-between"><span>{{ $methodLabels[$method->method] ?? ucfirst($method->method) }}</span><strong>{{ $percent }}%</strong></div>
              <div class="bg-gray-200 h-2.5 rounded-md mt-1.5"><div class="h-full bg-taxi rounded-md" style="width:{{ $percent }}%"></div></div>
            </div>
          @empty
            <p class="text-gray-500">Aucun paiement enregistré.</p>
          @endforelse
          <div class="h-px bg-gray-200 my-5"></div>
          <div class="flex justify-between"><span>Volume traité (mois)</span><strong class="text-xl">@fc($revenueMonth)</strong></div>
        </div>
      </div>
    </main>
  </div>
@endsection

@section('footer')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  (function () {
    const el = document.getElementById('adminActivityChart');
    if (!el || typeof Chart === 'undefined') return;

    new Chart(el, {
      data: {
        labels: @json($chartLabels),
        datasets: [
          {
            type: 'bar',
            label: 'Courses',
            data: @json($chartRides),
            backgroundColor: 'rgba(17, 20, 24, 0.85)',
            borderRadius: 6,
            yAxisID: 'yRides',
          },
          {
            type: 'line',
            label: 'Revenus (FC)',
            data: @json($chartRevenue),
            borderColor: '#f5b800',
            backgroundColor: 'rgba(255, 206, 0, 0.25)',
            borderWidth: 3,
            tension: 0.35,
            fill: true,
            yAxisID: 'yRevenue',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: function (ctx) {
                if (ctx.dataset.yAxisID === 'yRevenue') {
                  return ' Revenus : ' + ctx.parsed.y.toLocaleString('fr-FR') + ' FC';
                }
                return ' Courses : ' + ctx.parsed.y;
              },
            },
          },
        },
        scales: {
          yRides: { type: 'linear', position: 'left', beginAtZero: true, ticks: { precision: 0 } },
          yRevenue: {
            type: 'linear',
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            ticks: { callback: (v) => v.toLocaleString('fr-FR') },
          },
        },
      },
    });
  })();
</script>
@endsection
