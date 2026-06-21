@extends('mainPages.app')

@section('title', 'Suivi')

@section('content')
<div class="grid lg:grid-cols-[1fr_380px] lg:h-[calc(100vh-72px)]">
    <!-- Carte -->
    <div id="map" class="w-full h-[50vh] lg:h-full bg-gray-200 z-0"></div>

    <!-- Panneau -->
    <aside class="bg-white border-l border-gray-200 p-7 overflow-y-auto">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Course <span class="text-gray-500">#TG-1042</span></h2>
        <span id="ride-status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Chauffeur en route</span>
      </div>

      <div class="bg-ink text-white rounded-xl p-4 text-center mb-5">
        <div class="text-sm text-gray-400">Arrivée estimée dans</div>
        <div><span id="eta-value" class="text-4xl font-black text-taxi">8</span><span class="text-lg"> min</span></div>
      </div>

      <div class="flex items-center gap-3.5 p-4 bg-gray-50 rounded-xl mb-5">
        <div class="w-14 h-14 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">MD</div>
        <div class="flex-1">
          <strong>Marc Dubois</strong>
          <div class="text-sm text-gray-500">⭐ 4.92 · 2 480 courses</div>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-gray-200 p-4 mb-5">
        <div class="flex justify-between"><span class="text-gray-500">Véhicule</span><strong>Toyota Prius · Jaune</strong></div>
        <div class="flex justify-between mt-1"><span class="text-gray-500">Plaque</span><strong>AB-123-CD</strong></div>
      </div>

      <div class="flex gap-3 mb-6">
        <a href="tel:+33600000000" class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-full font-bold text-sm bg-ink text-white hover:bg-ink-soft transition">📞 Appeler</a>
        <a href="#" class="flex-1 inline-flex items-center justify-center px-4 py-2 rounded-full font-bold text-sm border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">💬 Message</a>
      </div>

      <h3 class="font-bold mb-4">Progression</h3>
      <div class="mb-2">
        <div class="track-step flex gap-3">
          <div class="flex flex-col items-center"><span class="track-dot w-3.5 h-3.5 rounded-full shrink-0 bg-green-500"></span><span class="track-line w-px flex-1 my-1 bg-green-500"></span></div>
          <div class="pb-6"><strong>Course confirmée</strong><div class="text-sm text-gray-500">Chauffeur assigné</div></div>
        </div>
        <div class="track-step flex gap-3">
          <div class="flex flex-col items-center"><span class="track-dot w-3.5 h-3.5 rounded-full shrink-0 bg-taxi animate-pulse"></span><span class="track-line w-px flex-1 my-1 bg-gray-200"></span></div>
          <div class="pb-6"><strong>Chauffeur en approche</strong><div class="text-sm text-gray-500">Vers le point de départ</div></div>
        </div>
        <div class="track-step flex gap-3">
          <div class="flex flex-col items-center"><span class="track-dot w-3.5 h-3.5 rounded-full shrink-0 bg-gray-300"></span><span class="track-line w-px flex-1 my-1 bg-gray-200"></span></div>
          <div class="pb-6"><strong>Course en cours</strong><div class="text-sm text-gray-500">En route vers la destination</div></div>
        </div>
        <div class="track-step flex gap-3">
          <div class="flex flex-col items-center"><span class="track-dot w-3.5 h-3.5 rounded-full shrink-0 bg-gray-300"></span></div>
          <div><strong>Arrivée</strong><div class="text-sm text-gray-500">À destination</div></div>
        </div>
      </div>

      <div class="h-px bg-gray-200 my-5"></div>
      <div class="flex justify-between"><span class="text-gray-500">📍 Départ</span><strong>Gare Centrale</strong></div>
      <div class="flex justify-between mt-1"><span class="text-gray-500">🏁 Arrivée</span><strong>Aéroport T2</strong></div>
      <div class="flex justify-between mt-1 text-lg"><span>Total</span><strong class="text-taxi-dark">32,50 €</strong></div>

      <button class="w-full mt-6 inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-red-600 text-white hover:bg-red-700 transition">Annuler la course</button>
    </aside>
  </div>
@endsection

@section('footer')
   <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection