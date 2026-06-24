@extends('mainPages.app' , ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Dashboard Chauffeur')

@section('header')
@show

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    <aside class="bg-ink text-gray-300 p-5 flex lg:flex-col gap-1 overflow-x-auto">
      @include('partials.brand-logo', ['class' => 'hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2'])
      <nav class="flex lg:flex-col gap-1 flex-1">
        <a href="{{ route('user.dashboardDriver') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap bg-taxi text-ink">📊 <span class="hidden lg:inline">Tableau de bord</span></a>
        <a href="#demandes" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🔔 <span class="hidden lg:inline">Demandes</span></a>
        <a href="{{ route('suivi') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🗺️ <span class="hidden lg:inline">Course active</span></a>
        <a href="{{ route('rides.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🕓 <span class="hidden lg:inline">Mes courses</span></a>
        <a href="#revenus" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">💰 <span class="hidden lg:inline">Revenus</span></a>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">👤 <span class="hidden lg:inline">Profil</span></a>
        <a href="{{ route('contact') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🛟 <span class="hidden lg:inline">Support</span></a>
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
        <div>
          <h1 class="text-2xl font-extrabold">Bonjour, {{ auth()->user()->firstname }} 🚖</h1>
          @if ($profile?->is_online)
            <p class="text-gray-500">Vous êtes <strong class="text-green-600">en ligne</strong> et disponible.</p>
          @else
            <p class="text-gray-500">Vous êtes <strong class="text-gray-500">hors ligne</strong>.</p>
          @endif
        </div>
        <div class="flex gap-3 items-center">
          @if ($profile)
            <span class="text-sm text-gray-500">{{ $profile->vehicle_model }} · {{ $profile->plate }}</span>
          @endif
          <div class="w-10 h-10 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">{{ strtoupper(substr(auth()->user()->firstname, 0, 1).substr(auth()->user()->lastname, 0, 1)) }}</div>
        </div>
      </div>

      @if (session('status'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-green-100 text-green-800 text-sm font-semibold">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-red-100 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
      @endif

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Revenus du jour</div><div class="text-3xl font-extrabold mt-1">@fc($revenueToday)</div><div class="text-gray-500 text-sm mt-1.5">{{ now()->translatedFormat('d M') }}</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Courses aujourd'hui</div><div class="text-3xl font-extrabold mt-1">{{ number_format($ridesToday, 0, ',', ' ') }}</div><div class="text-gray-500 text-sm mt-1.5">terminées</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Total courses</div><div class="text-3xl font-extrabold mt-1">{{ number_format($completedTotal, 0, ',', ' ') }}</div><div class="text-gray-500 text-sm mt-1.5">depuis le début</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Note</div><div class="text-3xl font-extrabold mt-1">{{ $profile?->rating ? number_format((float) $profile->rating, 2).'★' : '—' }}</div><div class="text-gray-500 text-sm mt-1.5">{{ number_format($ratingsCount, 0, ',', ' ') }} avis</div></div>
      </div>

      <!-- Demandes -->
      <div id="demandes" class="bg-white rounded-2xl p-6 shadow-soft border border-gray-200 border-l-4 border-l-taxi mb-6">
        <div class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">🔔 Demandes de courses disponibles</h2><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">{{ $pendingRequests->count() }} en attente</span></div>
        @forelse ($pendingRequests as $req)
          <div class="flex justify-between items-center flex-wrap gap-4 py-4 @if (! $loop->last) border-b border-gray-100 @endif">
            <div class="min-w-[220px]">
              <div class="flex justify-between gap-6"><span class="text-gray-500">📍 Prise en charge</span><strong class="text-right">{{ \Illuminate\Support\Str::before($req->pickup_addr, ',') }}</strong></div>
              <div class="flex justify-between gap-6 mt-1"><span class="text-gray-500">🏁 Destination</span><strong class="text-right">{{ \Illuminate\Support\Str::before($req->dropoff_addr, ',') }}</strong></div>
              <div class="flex justify-between gap-6 mt-1"><span class="text-gray-500">💰 Estimation</span><strong class="text-taxi-dark">@fc($req->price)@if ($req->distance_km) · {{ number_format((float) $req->distance_km, 1, ',', ' ') }} km @endif</strong></div>
            </div>
            <div class="flex gap-3">
              <a href="{{ route('rides.show', $req) }}" class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Détails</a>
              <form method="POST" action="{{ route('rides.accept', $req) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="inline-flex px-6 py-3 rounded-full font-bold bg-green-600 text-white hover:bg-green-700 transition">Accepter ✓</button>
              </form>
            </div>
          </div>
        @empty
          <p class="text-gray-500 py-4">Aucune demande de course en attente pour le moment.</p>
        @endforelse
      </div>

      <!-- Revenus -->
      <div id="revenus" class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Revenus de la semaine</h3>
          <div class="flex items-end gap-2.5 h-40">
            @foreach ($weekData as $day)
              <div class="flex-1 {{ $loop->last ? 'bg-taxi' : 'bg-gray-200' }} rounded-t-md" style="height:{{ max(round($day['total'] / $weekMax * 100), 3) }}%" title="{{ \App\Support\Money::fc($day['total']) }}"></div>
            @endforeach
          </div>
          <div class="flex justify-between mt-2 text-gray-500 text-xs">
            @foreach ($weekData as $day)
              <span>{{ $day['label'] }}</span>
            @endforeach
          </div>
          <div class="h-px bg-gray-200 my-5"></div>
          <div class="flex justify-between"><span>Total semaine</span><strong class="text-xl">@fc($weekTotal)</strong></div>
        </div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Mes dernières courses</h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3 font-bold">Date</th><th class="text-left px-4 py-3 font-bold">Trajet</th><th class="text-left px-4 py-3 font-bold">Gain</th></tr></thead>
              <tbody>
                @forelse ($recentRides as $ride)
                <tr class="border-t border-gray-200">
                  <td class="px-4 py-3 whitespace-nowrap">{{ $ride->completed_at?->translatedFormat('d/m H:i') }}</td>
                  <td class="px-4 py-3">{{ \Illuminate\Support\Str::before($ride->pickup_addr, ',') }} → {{ \Illuminate\Support\Str::before($ride->dropoff_addr, ',') }}</td>
                  <td class="px-4 py-3 whitespace-nowrap"><strong>@fc($ride->price)</strong></td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Aucune course terminée pour l'instant.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
@endsection

@section('footer')
@endsection
