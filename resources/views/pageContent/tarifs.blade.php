@extends('mainPages.app')

@section('title', 'Tarifs')

@section('content')
<section class="py-12 bg-ink text-white text-center">
    <div class="max-w-6xl mx-auto px-5">
      <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Tarifs transparents</span>
      <h1 class="mt-4 text-3xl font-extrabold">Des prix clairs, sans surprise</h1>
      <p class="text-gray-400">Choisissez la formule adaptée à vos besoins.</p>
    </div>
  </section>

  <section class="py-20">
    <div class="max-w-6xl mx-auto px-5 grid md:grid-cols-3 gap-6 items-center">
      <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center">
        <h3 class="text-lg font-bold">Éco 🚗</h3>
        <div class="text-5xl font-black mt-3">2,50€ <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 1,10 € / km</p>
        <ul class="my-6 text-left">
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ 1 à 4 passagers</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Suivi en temps réel</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Paiement en ligne</li>
          <li class="flex gap-2.5 py-2">✓ Annulation gratuite</li>
        </ul>
        <a href="reservation.html" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Réserver</a>
      </div>
      <div class="relative bg-white rounded-2xl p-7 shadow-lg2 border-2 border-taxi text-center scale-105">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-taxi text-ink px-4.5 py-1.5 rounded-full text-xs font-extrabold px-5">Le plus choisi</div>
        <h3 class="text-lg font-bold">Confort 🚙</h3>
        <div class="text-5xl font-black mt-3">4,00€ <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 1,60 € / km</p>
        <ul class="my-6 text-left">
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Véhicule haut de gamme</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Eau & chargeurs offerts</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Suivi en temps réel</li>
          <li class="flex gap-2.5 py-2">✓ Chauffeur premium noté 4.9+</li>
        </ul>
        <a href="reservation.html" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réserver</a>
      </div>
      <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center">
        <h3 class="text-lg font-bold">Van 🚐</h3>
        <div class="text-5xl font-black mt-3">6,00€ <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 2,20 € / km</p>
        <ul class="my-6 text-left">
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Jusqu'à 7 passagers</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Grands bagages</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100">✓ Idéal groupes & aéroport</li>
          <li class="flex gap-2.5 py-2">✓ Suivi en temps réel</li>
        </ul>
        <a href="reservation.html" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Réserver</a>
      </div>
    </div>
  </section>

  <section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-5">
      <h2 class="text-3xl font-extrabold text-center mb-10">Suppléments éventuels</h2>
      <div class="overflow-x-auto rounded-xl border border-gray-200 max-w-2xl mx-auto">
        <table class="w-full">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3.5 font-bold">Supplément</th><th class="text-left px-4 py-3.5 font-bold">Tarif</th></tr></thead>
          <tbody>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Course de nuit (22h-6h)</td><td class="px-4 py-3.5">+15%</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Bagage volumineux</td><td class="px-4 py-3.5">+2,00 €</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Attente (par minute)</td><td class="px-4 py-3.5">0,40 €</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Animal de compagnie</td><td class="px-4 py-3.5">+3,00 €</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Péage / parking</td><td class="px-4 py-3.5">Au réel</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
@endsection