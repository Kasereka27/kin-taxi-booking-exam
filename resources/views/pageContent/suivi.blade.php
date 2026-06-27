@extends('mainPages.app')

@section('title', 'Suivi')

@section('content')
@if ($ride)
  @php
    $driver = $ride->driver;
    $profile = $driver?->driverProfile;
    $statusStyles = match ($ride->status) {
        'pending' => ['En attente d\'un chauffeur', 'bg-gray-100 text-gray-700'],
        'assigned' => ['Chauffeur assigné', 'bg-blue-100 text-blue-700'],
        'approche' => ['Chauffeur en route', 'bg-blue-100 text-blue-700'],
        'course' => ['Course en cours', 'bg-yellow-100 text-yellow-700'],
        default => [$ride->statusLabel(), 'bg-gray-100 text-gray-700'],
    };
    $stepIndex = match ($ride->status) {
        'approche' => 1,
        'course' => 2,
        default => 0,
    };
    $etaMinutes = $ride->distance_km
        ? max(3, (int) round((float) $ride->distance_km * 1.8 + 3))
        : 8;
    $driverInitials = $driver
        ? strtoupper(substr($driver->firstname, 0, 1).substr($driver->lastname, 0, 1))
        : '—';
  @endphp
  <div class="grid lg:grid-cols-[1fr_380px] lg:h-[calc(100vh-72px)]">
    <div id="map" class="w-full h-[50vh] lg:h-full bg-gray-200 z-0"></div>

    <aside class="bg-white border-l border-gray-200 p-5 sm:p-7 overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Course <span class="text-gray-500">#{{ $ride->reference() }}</span></h2>
        <span id="ride-status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles[1] }}">{{ $statusStyles[0] }}</span>
      </div>

      @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-green-100 text-green-800 text-sm font-semibold">{{ session('success') }}</div>
      @endif

      <div class="bg-ink text-white rounded-xl p-4 text-center mb-5">
        <div id="route-phase-label" class="text-sm text-gray-400">Arrivée estimée dans</div>
        <div><span id="eta-value" class="text-4xl font-black text-taxi">{{ $etaMinutes }}</span><span class="text-lg"> min</span></div>
        <div id="route-distance" class="text-xs text-gray-400 mt-1"></div>
      </div>

      @if ($driver)
        <div class="flex items-center gap-3.5 p-4 bg-gray-50 rounded-xl mb-5">
          <div class="w-14 h-14 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">{{ $driverInitials }}</div>
          <div class="flex-1">
            <strong>{{ $driver->firstname }} {{ $driver->lastname }}</strong>
            <div class="text-sm text-gray-500">
              @if ($profile?->rating)
                <span class="inline-flex items-center gap-1"><x-icon name="star-solid" class="w-4 h-4 text-taxi" /> {{ number_format((float) $profile->rating, 2, ',', ' ') }}</span>
              @else
                Chauffeur vérifié
              @endif
            </div>
          </div>
        </div>
        @if ($profile)
          <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
            <div class="flex justify-between"><span class="text-gray-500">Véhicule</span><strong>{{ $profile->vehicle_model }}</strong></div>
            <div class="flex justify-between mt-1"><span class="text-gray-500">Plaque</span><strong>{{ $profile->plate }}</strong></div>
          </div>
        @endif
        <div class="flex gap-3 mb-6">
          @if ($driver->phone)
            <a href="tel:{{ $driver->phone }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold text-sm bg-ink text-white hover:bg-ink-soft transition"><x-icon name="phone" class="w-4 h-4" /> Appeler</a>
            <a href="sms:{{ $driver->phone }}" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-full font-bold text-sm border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition"><x-icon name="chat-bubble-left" class="w-4 h-4" /> Message</a>
          @endif
        </div>
      @else
        <div class="bg-yellow-50 text-yellow-800 rounded-xl p-4 mb-5 text-sm">
          Recherche d'un chauffeur disponible… Vous serez notifié dès qu'une course est acceptée.
        </div>
      @endif

      <h3 class="font-bold mb-4">Progression</h3>
      <div class="mb-2" id="track-steps">
        @foreach ([
          ['Course confirmée', $ride->status === 'pending' ? 'Recherche d\'un chauffeur' : 'Chauffeur assigné'],
          ['Chauffeur en approche', 'Vers le point de départ'],
          ['Course en cours', 'En route vers la destination'],
          ['Arrivée', 'À destination'],
        ] as $index => [$title, $subtitle])
          <div class="track-step flex gap-3">
            <div class="flex flex-col items-center">
              <span class="track-dot w-3.5 h-3.5 rounded-full shrink-0 {{ $index < $stepIndex ? 'bg-green-500' : ($index === $stepIndex ? 'bg-taxi animate-pulse' : 'bg-gray-300') }}"></span>
              @if (! $loop->last)
                <span class="track-line w-px flex-1 my-1 {{ $index < $stepIndex ? 'bg-green-500' : 'bg-gray-200' }}"></span>
              @endif
            </div>
            <div class="{{ $loop->last ? '' : 'pb-6' }}">
              <strong>{{ $title }}</strong>
              <div class="text-sm text-gray-500">{{ $subtitle }}</div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="h-px bg-gray-200 my-5"></div>
      <div class="flex justify-between gap-3"><span class="text-gray-500 inline-flex items-center gap-1.5 shrink-0"><x-icon name="map-pin" class="w-4 h-4" /> Départ</span><strong class="text-right max-w-[60%]">{{ $ride->pickup_addr }}</strong></div>
      <div class="flex justify-between gap-3 mt-1"><span class="text-gray-500 inline-flex items-center gap-1.5 shrink-0"><x-icon name="flag" class="w-4 h-4" /> Arrivée</span><strong class="text-right max-w-[60%]">{{ $ride->dropoff_addr }}</strong></div>
      <div class="flex justify-between mt-1 text-lg"><span>Total</span><strong class="text-taxi-dark">@fc($ride->price)</strong></div>

      @can('update', $ride)
        <form method="POST" action="{{ route('rides.cancel', $ride) }}" class="mt-6" onsubmit="return confirm('Annuler cette course ?');">
          @csrf
          @method('PATCH')
          <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-red-600 text-white hover:bg-red-700 transition">Annuler la course</button>
        </form>
      @endcan

      <div class="mt-4 text-center">
        <a href="{{ route('rides.show', $ride) }}" class="text-taxi-dark font-semibold text-sm">Voir les détails →</a>
      </div>
    </aside>
  </div>
@elseif (isset($inactiveRide))
  <section class="py-16">
    <div class="max-w-lg mx-auto px-5 text-center">
      <div class="bg-white rounded-2xl p-8 shadow-soft border border-gray-200">
        <div class="flex justify-center mb-3 text-green-600"><x-icon name="check-circle" class="w-14 h-14" /></div>
        <h1 class="text-xl font-extrabold">Course #{{ $inactiveRide->reference() }}</h1>
        <p class="text-gray-500 mt-2">Cette course n'est plus suivie en direct (statut : {{ $inactiveRide->statusLabel() }}).</p>
        <a href="{{ route('rides.show', $inactiveRide) }}" class="mt-6 inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Voir la course</a>
      </div>
    </div>
  </section>
@else
  <section class="py-16">
    <div class="max-w-lg mx-auto px-5 text-center">
      <div class="bg-white rounded-2xl p-8 shadow-soft border border-gray-200">
        <div class="flex justify-center mb-3 text-taxi-dark"><x-icon name="signal" class="w-14 h-14" /></div>
        <h1 class="text-xl font-extrabold">Aucune course à suivre</h1>
        <p class="text-gray-500 mt-2">
          @auth
            Vous n'avez pas de course active pour le moment.
          @else
            Connectez-vous pour suivre votre course en temps réel.
          @endauth
        </p>
        <div class="flex flex-wrap justify-center gap-3 mt-6">
          @auth
            <a href="{{ route('reservation') }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réserver une course</a>
            <a href="{{ route('rides.index') }}" class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink transition">Mon historique</a>
          @else
            <a href="{{ route('login') }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Se connecter</a>
            <a href="{{ route('reservation') }}" class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink transition">Réserver</a>
          @endauth
        </div>
      </div>
    </div>
  </section>
@endif
@endsection

@if ($ride)
@section('scripts')
@php
  $trackingPayload = [
      'rideId' => $ride->id,
      'pickup' => $ride->pickupCoordinates(),
      'dropoff' => $ride->dropoffCoordinates(),
      'driver' => $ride->driverCoordinates(),
      'status' => $ride->status,
      'animate' => $ride->status !== 'pending' && $ride->driver_id !== null,
      'isDriver' => auth()->check() && auth()->id() === $ride->driver_id,
      'trackingUrl' => route('rides.tracking', $ride),
  ];
@endphp
<script>
  window.trackingRide = @json($trackingPayload);
</script>
@endsection
@endif
