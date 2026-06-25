@extends('mainPages.app')

@section('title', 'Accueil')

@section('content')
    <!-- Hero -->
  <section class="relative overflow-hidden bg-ink text-white py-24" style="background-image:radial-gradient(circle at 80% 20%, rgba(255,206,0,0.18), transparent 45%)">
    <div class="max-w-6xl mx-auto px-5 grid lg:grid-cols-2 gap-12 items-center">
      <div>
        <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Disponible 24h/24 · 7j/7</span>
        <h1 class="mt-4 text-5xl font-black leading-tight tracking-tight">Votre taxi en <span class="text-taxi">3 clics</span>, suivi en <span class="text-taxi">temps réel</span></h1>
        <p class="mt-5 text-lg text-gray-300 max-w-lg">Commandez une course, payez en ligne et suivez la position de votre chauffeur en direct sur la carte. Simple, rapide et fiable.</p>
        <div class="flex flex-wrap gap-3.5 mt-8">
          <a href="{{ route('reservation') }}" class="inline-flex items-center px-8 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark hover:-translate-y-0.5 transition">Réserver maintenant</a>
          <a href="{{ route('suivi') }}" class="inline-flex items-center px-8 py-4 rounded-full font-bold text-lg border-2 border-gray-700 text-white hover:bg-white hover:text-ink transition">Voir le suivi en direct</a>
        </div>
        <div class="flex gap-10 mt-12">
          <div><strong class="block text-3xl text-taxi">120k+</strong><span class="text-gray-400 text-sm">Courses / mois</span></div>
          <div><strong class="block text-3xl text-taxi">3 min</strong><span class="text-gray-400 text-sm">Temps d'attente moyen</span></div>
          <div><strong class="block text-3xl text-taxi">4.9★</strong><span class="text-gray-400 text-sm">Note moyenne</span></div>
        </div>
      </div>
      <form action="{{ route('reservation') }}" method="GET" class="bg-white text-ink rounded-2xl p-7 shadow-lg2">
        <h3 class="text-xl font-bold mb-4">Estimer ma course</h3>
        <div class="mb-4">
          <label for="hero-pickup" class="block font-semibold mb-1.5 text-sm">Départ</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">📍</span><input id="hero-pickup" name="pickup_addr" class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Adresse de départ" /></div>
        </div>
        <div class="mb-4">
          <label for="hero-dropoff" class="block font-semibold mb-1.5 text-sm">Destination</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">🏁</span><input id="hero-dropoff" name="dropoff_addr" class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Adresse d'arrivée" /></div>
        </div>
        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-ink text-white hover:bg-ink-soft transition">Estimer le prix →</button>
        <p class="text-center text-sm text-gray-500 mt-3">Sans engagement · Annulation gratuite</p>
      </form>
    </div>
  </section>

  <!-- Avantages -->
  <section class="py-20">
    <div class="max-w-6xl mx-auto px-5">
      <div class="text-center mb-10">
        <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Pourquoi KinTaxiBooking</span>
        <h2 class="mt-4 text-3xl font-extrabold">Tout ce qu'il faut pour voyager sereinement</h2>
        <p class="text-gray-500 mt-3 max-w-xl mx-auto">Une expérience pensée pour le confort et la confiance des passagers comme des chauffeurs.</p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-2xl mb-4">📡</div><h3 class="text-lg font-bold mb-2">Suivi en direct</h3><p class="text-gray-500">Visualisez la position exacte de votre chauffeur et l'heure d'arrivée estimée.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-2xl mb-4">💳</div><h3 class="text-lg font-bold mb-2">Paiement sécurisé</h3><p class="text-gray-500">Carte, mobile ou espèces. Vos données sont chiffrées de bout en bout.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-2xl mb-4">⭐</div><h3 class="text-lg font-bold mb-2">Chauffeurs notés</h3><p class="text-gray-500">Des professionnels vérifiés et évalués par la communauté.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-2xl mb-4">🛟</div><h3 class="text-lg font-bold mb-2">Support 24/7</h3><p class="text-gray-500">Une équipe disponible à tout moment en cas de besoin.</p></div>
      </div>
    </div>
  </section>

  <!-- Comment ça marche -->
  <section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-5">
      <div class="text-center mb-10">
        <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">En 4 étapes</span>
        <h2 class="mt-4 text-3xl font-extrabold">Comment ça marche ?</h2>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200"><div class="w-11 h-11 rounded-full bg-ink text-taxi grid place-items-center font-extrabold mb-4">1</div><h3 class="text-lg font-bold mb-2">Indiquez le trajet</h3><p class="text-gray-500">Saisissez votre départ et votre destination.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200"><div class="w-11 h-11 rounded-full bg-ink text-taxi grid place-items-center font-extrabold mb-4">2</div><h3 class="text-lg font-bold mb-2">Choisissez le véhicule</h3><p class="text-gray-500">Éco, Confort ou Van selon vos besoins.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200"><div class="w-11 h-11 rounded-full bg-ink text-taxi grid place-items-center font-extrabold mb-4">3</div><h3 class="text-lg font-bold mb-2">Suivez le chauffeur</h3><p class="text-gray-500">En temps réel, jusqu'à votre porte.</p></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200"><div class="w-11 h-11 rounded-full bg-ink text-taxi grid place-items-center font-extrabold mb-4">4</div><h3 class="text-lg font-bold mb-2">Payez & notez</h3><p class="text-gray-500">Paiement automatique et évaluation.</p></div>
      </div>
    </div>
  </section>

  <!-- Flotte -->
  <section class="py-20">
    <div class="max-w-6xl mx-auto px-5">
      <div class="text-center mb-10"><span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Notre flotte</span><h2 class="mt-4 text-3xl font-extrabold">Un véhicule pour chaque trajet</h2></div>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="text-5xl">🚗</div><h3 class="text-lg font-bold mt-2">Éco</h3><p class="text-gray-500">Économique et écologique, idéal en ville.</p><div class="inline-flex mt-3 px-3 py-1 rounded-full text-xs font-bold bg-taxi/20 text-taxi-dark">Dès 2 000 FC</div></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="text-5xl">🚙</div><h3 class="text-lg font-bold mt-2">Confort</h3><p class="text-gray-500">Plus d'espace et de standing pour vos déplacements.</p><div class="inline-flex mt-3 px-3 py-1 rounded-full text-xs font-bold bg-taxi/20 text-taxi-dark">Dès 3 500 FC</div></div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="text-5xl">🚐</div><h3 class="text-lg font-bold mt-2">Van</h3><p class="text-gray-500">Jusqu'à 7 passagers et de grands bagages.</p><div class="inline-flex mt-3 px-3 py-1 rounded-full text-xs font-bold bg-taxi/20 text-taxi-dark">Dès 5 000 FC</div></div>
      </div>
    </div>
  </section>

  <!-- CTA chauffeur -->
  <section class="py-20 bg-ink text-white">
    <div class="max-w-6xl mx-auto px-5 flex flex-wrap justify-between items-center gap-6">
      <div>
        <h2 class="text-3xl font-extrabold">Devenez chauffeur partenaire</h2>
        <p class="text-gray-400 max-w-lg mt-2.5">Gérez vos courses, vos revenus et votre planning depuis un tableau de bord dédié.</p>
      </div>
      @auth
        @if (auth()->user()->isDriver())
          <a href="{{ route('user.dashboardDriver') }}" class="inline-flex items-center px-8 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Espace chauffeur</a>
        @else
          <a href="{{ route('register', ['role' => 'driver']) }}" class="inline-flex items-center px-8 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Devenir chauffeur</a>
        @endif
      @else
        <a href="{{ route('register', ['role' => 'driver']) }}" class="inline-flex items-center px-8 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Espace chauffeur</a>
      @endauth
    </div>
  </section>
@endsection
