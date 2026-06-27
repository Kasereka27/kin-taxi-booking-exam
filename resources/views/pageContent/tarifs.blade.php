@extends('mainPages.app')

@section('title', 'Tarifs')

@section('content')
<section class="py-8 sm:py-12 bg-ink text-white text-center">
    <div class="max-w-6xl mx-auto px-4 sm:px-5">
      <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Tarifs transparents</span>
      <h1 class="mt-4 text-2xl sm:text-3xl font-extrabold">Des prix clairs, sans surprise</h1>
      <p class="text-gray-400">Choisissez la formule adaptée à vos besoins.</p>
    </div>
  </section>

  <section class="py-12 sm:py-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-5 grid md:grid-cols-3 gap-6 items-stretch">
      <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center flex flex-col">
        <div class="flex items-center justify-center gap-2"><x-vehicle-icon type="eco" class="w-8 h-8" /><h3 class="text-lg font-bold">Éco</h3></div>
        <div class="text-3xl sm:text-5xl font-black mt-3">2 000 FC <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 800 FC / km</p>
        <ul class="my-6 text-left flex-1">
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> 1 à 4 passagers</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Suivi en temps réel</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Paiement en ligne</li>
          <li class="flex gap-2.5 py-2 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Annulation gratuite</li>
        </ul>
        <a href="{{ route('reservation') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Réserver</a>
      </div>
      <div class="relative bg-white rounded-2xl p-6 sm:p-7 shadow-lg2 border-2 border-taxi text-center flex flex-col md:scale-105">
        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-taxi text-ink px-5 py-1.5 rounded-full text-xs font-extrabold">Le plus choisi</div>
        <div class="flex items-center justify-center gap-2"><x-vehicle-icon type="confort" class="w-8 h-8" /><h3 class="text-lg font-bold">Confort</h3></div>
        <div class="text-3xl sm:text-5xl font-black mt-3">3 500 FC <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 1 200 FC / km</p>
        <ul class="my-6 text-left flex-1">
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Véhicule haut de gamme</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Eau & chargeurs offerts</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Suivi en temps réel</li>
          <li class="flex gap-2.5 py-2 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Chauffeur premium noté 4.9+</li>
        </ul>
        <a href="{{ route('reservation') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réserver</a>
      </div>
      <div class="bg-white rounded-2xl p-6 sm:p-7 shadow-soft border border-gray-200 text-center flex flex-col">
        <div class="flex items-center justify-center gap-2"><x-vehicle-icon type="van" class="w-8 h-8" /><h3 class="text-lg font-bold">Van</h3></div>
        <div class="text-3xl sm:text-5xl font-black mt-3">5 000 FC <span class="text-base text-gray-500 font-medium">/ prise en charge</span></div>
        <p class="text-gray-500">+ 1 800 FC / km</p>
        <ul class="my-6 text-left flex-1">
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Jusqu'à 7 passagers</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Grands bagages</li>
          <li class="flex gap-2.5 py-2 border-b border-gray-100 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Idéal groupes & aéroport</li>
          <li class="flex gap-2.5 py-2 items-start"><x-icon name="check" class="w-5 h-5 text-green-500 shrink-0" /> Suivi en temps réel</li>
        </ul>
        <a href="{{ route('reservation') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Réserver</a>
      </div>
    </div>
  </section>

  <section class="py-12 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-5">
      <h2 class="text-2xl sm:text-3xl font-extrabold text-center mb-10">Suppléments éventuels</h2>
      <div class="overflow-x-auto rounded-xl border border-gray-200 max-w-2xl mx-auto">
        <table class="w-full min-w-[320px]">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3.5 font-bold">Supplément</th><th class="text-left px-4 py-3.5 font-bold">Tarif</th></tr></thead>
          <tbody>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Course de nuit (22h-6h)</td><td class="px-4 py-3.5">+15%</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Bagage volumineux</td><td class="px-4 py-3.5">+5 000 FC</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Attente (par minute)</td><td class="px-4 py-3.5">800 FC</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Animal de compagnie</td><td class="px-4 py-3.5">+6 000 FC</td></tr>
            <tr class="border-t border-gray-200"><td class="px-4 py-3.5">Péage / parking</td><td class="px-4 py-3.5">Au réel</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
@endsection
