@extends('mainPages.dashboard')

@section('title', 'Dashboard Client')

@section('header')
@show

@section('content')
<x-dashboard-shell>
  <x-slot name="sidebar">
    <aside class="w-full h-full lg:min-h-full bg-ink text-gray-300 p-4 sm:p-5 flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-x-hidden lg:overflow-y-auto min-w-0">
      @include('partials.brand-logo', ['class' => 'hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2'])
      <nav class="flex lg:flex-col gap-1 flex-1">
        <x-dashboard-nav-link :href="route('user.dashboardClient')" icon="chart-bar" label="Tableau de bord" :active="true" />
        <x-dashboard-nav-link :href="route('reservation')" icon="plus" label="Nouvelle course" />
        <x-dashboard-nav-link :href="route('suivi')" icon="signal" label="Suivi en direct" />
        <x-dashboard-nav-link :href="route('rides.index')" icon="clock" label="Historique" />
        <x-dashboard-nav-link :href="route('rides.index', ['status' => 'completed'])" icon="credit-card" label="Paiement" />
        <x-dashboard-nav-link :href="route('profile.edit')" icon="user" label="Profil" />
        <x-dashboard-nav-link :href="route('contact')" icon="lifebuoy" label="Aide" />
      </nav>
      <div class="hidden lg:block border-t border-gray-800 pt-4 mt-4">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition"><x-icon name="arrow-right-on-rectangle" class="w-5 h-5 shrink-0" /> Déconnexion</button>
        </form>
      </div>
    </aside>
  </x-slot>

      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl font-extrabold">Bonjour, {{ auth()->user()->firstname }}</h1>
          <p class="text-gray-500">Voici un aperçu de votre activité.</p>
        </div>
        <div class="flex gap-3 items-center">
          <a href="{{ route('reservation') }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">+ Réserver</a>
          <div class="w-10 h-10 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">{{ strtoupper(substr(auth()->user()->firstname, 0, 1).substr(auth()->user()->lastname, 0, 1)) }}</div>
        </div>
      </div>

      @if (session('success'))
        <div class="mb-6 px-4 py-3 rounded-lg text-sm bg-green-100 text-green-700 font-medium">{{ session('success') }}</div>
      @endif

      <!-- Stats -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Courses totales</div><div class="text-3xl font-extrabold mt-1">{{ $totalRides }}</div><div class="text-gray-500 text-sm mt-1.5">depuis l'inscription</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Dépenses du mois</div><div class="text-3xl font-extrabold mt-1">@fc($monthSpend)</div><div class="text-gray-500 text-sm mt-1.5">{{ now()->translatedFormat('F Y') }}</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Distance parcourue</div><div class="text-3xl font-extrabold mt-1">{{ number_format((float) $totalDistance, 0, ',', ' ') }} km</div><div class="text-gray-500 text-sm mt-1.5">courses terminées</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Note moyenne donnée</div><div class="text-3xl font-extrabold mt-1 inline-flex items-center gap-1">{{ $avgRatingGiven ? number_format((float) $avgRatingGiven, 1) : '—' }}@if ($avgRatingGiven)<x-icon name="star-solid" class="w-5 h-5 text-taxi" />@endif</div><div class="text-gray-500 text-sm mt-1.5">Merci !</div></div>
      </div>

      <!-- Course en cours -->
      @if ($currentRide)
        <div class="bg-white rounded-2xl p-6 shadow-soft border border-gray-200 border-l-4 border-l-taxi mb-6">
          <div class="flex justify-between items-center flex-wrap gap-3">
            <div class="flex gap-3 items-center">
              <span class="w-2 h-2 rounded-full bg-green-500 ring-4 ring-green-100 animate-pulse"></span>
              <div>
                <strong>Course en cours · #{{ $currentRide->reference() }}</strong>
                <div class="text-gray-500 text-sm">{{ $currentRide->pickup_addr }} → {{ $currentRide->dropoff_addr }}@if ($currentRide->driver) · {{ $currentRide->driver->firstname }} {{ $currentRide->driver->lastname }}@endif</div>
              </div>
            </div>
            <a href="{{ route('suivi.ride', $currentRide) }}" class="inline-flex px-4 py-2 rounded-full font-bold text-sm bg-ink text-white hover:bg-ink-soft transition">Suivre en direct →</a>
          </div>
        </div>
      @endif

      <!-- Historique -->
      <div class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">Courses récentes</h2><a href="{{ route('rides.index') }}" class="text-taxi-dark font-semibold">Tout voir</a></div>
      <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full bg-white">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
            <th class="text-left px-4 py-3.5 font-bold">Réf.</th><th class="text-left px-4 py-3.5 font-bold">Date</th><th class="text-left px-4 py-3.5 font-bold">Trajet</th><th class="text-left px-4 py-3.5 font-bold">Chauffeur</th><th class="text-left px-4 py-3.5 font-bold">Montant</th><th class="text-left px-4 py-3.5 font-bold">Statut</th><th class="text-left px-4 py-3.5 font-bold">Action</th>
          </tr></thead>
          <tbody>
            @forelse ($recentRides as $ride)
              <tr class="border-t border-gray-200 hover:bg-gray-50">
                <td class="px-4 py-3.5"><strong>{{ $ride->reference() }}</strong></td>
                <td class="px-4 py-3.5">{{ $ride->created_at->format('d/m H:i') }}</td>
                <td class="px-4 py-3.5">{{ $ride->pickup_addr }} → {{ $ride->dropoff_addr }}</td>
                <td class="px-4 py-3.5">{{ $ride->driver ? $ride->driver->firstname.' '.$ride->driver->lastname : '—' }}</td>
                <td class="px-4 py-3.5">@fc($ride->price)</td>
                <td class="px-4 py-3.5">
                  @php($cls = match ($ride->status) { 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-yellow-100 text-yellow-700' })
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ $ride->statusLabel() }}</span>
                </td>
                <td class="px-4 py-3.5">
                  @if ($ride->isPayable())
                    <a href="{{ route('rides.pay', $ride) }}" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Payer</a>
                  @elseif ($ride->status === 'completed')
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Payée</span>
                  @else
                    <a href="{{ route('rides.show', $ride) }}" class="text-taxi-dark font-semibold text-sm">Voir</a>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucune course pour le moment. <a href="{{ route('reservation') }}" class="text-taxi-dark font-semibold">Réservez votre première course →</a></td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
</x-dashboard-shell>
@endsection

@section('footer')
@endsection