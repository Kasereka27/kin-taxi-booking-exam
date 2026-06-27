@extends('mainPages.app')

@section('title', 'À propos')

@section('content')
<section class="relative overflow-hidden bg-ink text-white py-12 sm:py-16" style="background-image:radial-gradient(circle at 80% 20%, rgba(255,206,0,0.18), transparent 45%)">
    <div class="max-w-6xl mx-auto px-4 sm:px-5 grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <div>
        <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Notre histoire</span>
        <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-black leading-tight">La mobilité urbaine, <span class="text-taxi">réinventée</span></h1>
        <p class="mt-5 text-base sm:text-lg text-gray-300 max-w-lg">Depuis 2024, KinTaxiBooking connecte passagers et chauffeurs grâce à une technologie de suivi en temps réel, pour des trajets simples, sûrs et transparents.</p>
      </div>
      <div class="bg-white text-ink rounded-2xl p-5 sm:p-7 shadow-lg2">
        <div class="grid grid-cols-2 gap-4">
          <div class="text-center"><div class="text-2xl sm:text-3xl font-black text-taxi-dark">120k+</div><div class="text-gray-500 text-sm">Courses / mois</div></div>
          <div class="text-center"><div class="text-2xl sm:text-3xl font-black text-taxi-dark">5 000</div><div class="text-gray-500 text-sm">Chauffeurs</div></div>
          <div class="text-center"><div class="text-2xl sm:text-3xl font-black text-taxi-dark">28</div><div class="text-gray-500 text-sm">Villes</div></div>
          <div class="text-center"><div class="inline-flex items-center justify-center gap-1"><span class="text-2xl sm:text-3xl font-black text-taxi-dark">4.9</span><x-icon name="star-solid" class="w-5 h-5 text-taxi-dark" /></div><div class="text-gray-500 text-sm">Satisfaction</div></div>
        </div>
      </div>
    </div>
  </section>

  <section class="py-12 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-5">
      <div class="text-center mb-10"><h2 class="text-2xl sm:text-3xl font-extrabold">Nos valeurs</h2></div>
      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-taxi-dark mb-4"><x-icon name="shield-check" class="w-7 h-7" /></div><h3 class="text-lg font-bold mb-2">Sécurité</h3><p class="text-gray-500">Chauffeurs vérifiés, suivi GPS et partage de trajet pour des courses en toute confiance.</p></div>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-taxi-dark mb-4"><x-icon name="sparkles" class="w-7 h-7" /></div><h3 class="text-lg font-bold mb-2">Transparence</h3><p class="text-gray-500">Des prix clairs affichés avant chaque course, sans frais cachés.</p></div>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 hover:-translate-y-1.5 hover:shadow-lg2 transition"><div class="w-14 h-14 rounded-2xl bg-taxi/20 grid place-items-center text-taxi-dark mb-4"><x-icon name="leaf" class="w-7 h-7" /></div><h3 class="text-lg font-bold mb-2">Durabilité</h3><p class="text-gray-500">Une flotte de plus en plus électrique pour réduire notre empreinte carbone.</p></div>
      </div>
    </div>
  </section>

  <section class="py-12 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-5">
      <div class="text-center mb-10"><h2 class="text-2xl sm:text-3xl font-extrabold">L'équipe fondatrice</h2></div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center"><div class="w-20 h-20 rounded-full bg-taxi grid place-items-center text-2xl font-extrabold text-ink mx-auto">AL</div><h3 class="text-lg font-bold mt-3">Alice Leroy</h3><p class="text-gray-500">CEO & Co-fondatrice</p></div>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center"><div class="w-20 h-20 rounded-full bg-taxi grid place-items-center text-2xl font-extrabold text-ink mx-auto">TM</div><h3 class="text-lg font-bold mt-3">Thomas Martin</h3><p class="text-gray-500">CTO</p></div>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center"><div class="w-20 h-20 rounded-full bg-taxi grid place-items-center text-2xl font-extrabold text-ink mx-auto">NK</div><h3 class="text-lg font-bold mt-3">Nadia Kane</h3><p class="text-gray-500">Directrice Opérations</p></div>
        <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center"><div class="w-20 h-20 rounded-full bg-taxi grid place-items-center text-2xl font-extrabold text-ink mx-auto">SP</div><h3 class="text-lg font-bold mt-3">Sam Petit</h3><p class="text-gray-500">Directeur Produit</p></div>
      </div>
    </div>
  </section>

  <section class="py-12 sm:py-20 bg-ink text-white text-center">
    <div class="max-w-6xl mx-auto px-4 sm:px-5">
      <h2 class="text-2xl sm:text-3xl font-extrabold">Prêt à voyager avec KinTaxiBooking ?</h2>
      <p class="text-gray-400 mt-2.5">Rejoignez des milliers d'utilisateurs satisfaits.</p>
      <a href="{{ route('reservation') }}" class="inline-flex items-center px-6 sm:px-8 py-3.5 sm:py-4 rounded-full font-bold text-base sm:text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition mt-6">Réserver une course</a>
    </div>
  </section>
@endsection
