@extends('mainPages.app' , ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Dashboard Client')

@section('header')
@show

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    <aside class="bg-ink text-gray-300 p-5 flex lg:flex-col gap-1 overflow-x-auto">
      <a href="{{ route('home') }}" class="hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2"><span class="w-9 h-9 rounded-xl bg-taxi grid place-items-center shadow-taxi text-xl">🚕</span> Taxi<span class="text-taxi-dark">Go</span></a>
      <nav class="flex lg:flex-col gap-1 flex-1">
        <a href="{{ route('user.dashboardDriver') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap bg-taxi text-ink">📊 <span class="hidden lg:inline">Tableau de bord</span></a>
        <a href="#demandes" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🔔 <span class="hidden lg:inline">Demandes</span></a>
        <a href="{{ route('suivi') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🗺️ <span class="hidden lg:inline">Course active</span></a>
        <a href="{{ route('rides.index') }}" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🕓 <span class="hidden lg:inline">Mes courses</span></a>
        <a href="#revenus" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">💰 <span class="hidden lg:inline">Revenus</span></a>
        <a href="#" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🚗 <span class="hidden lg:inline">Mon véhicule</span></a>
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
          <p class="text-gray-500">Vous êtes <strong class="text-green-600">en ligne</strong> et disponible.</p>
        </div>
        <div class="flex gap-3 items-center">
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" checked /> Disponible</label>
          <div class="w-10 h-10 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">{{ strtoupper(substr(auth()->user()->firstname, 0, 1).substr(auth()->user()->lastname, 0, 1)) }}</div>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Revenus du jour</div><div class="text-3xl font-extrabold mt-1">186 000 FC</div><div class="text-green-600 text-sm font-semibold mt-1.5">▲ 22%</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Courses aujourd'hui</div><div class="text-3xl font-extrabold mt-1">11</div><div class="text-green-600 text-sm font-semibold mt-1.5">▲ 3</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Heures en ligne</div><div class="text-3xl font-extrabold mt-1">6h 20</div><div class="text-gray-500 text-sm mt-1.5">aujourd'hui</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Note</div><div class="text-3xl font-extrabold mt-1">4.92★</div><div class="text-gray-500 text-sm mt-1.5">2 480 courses</div></div>
      </div>

      <!-- Demande -->
      <div id="demandes" class="bg-white rounded-2xl p-6 shadow-soft border border-gray-200 border-l-4 border-l-taxi mb-6">
        <div class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">🔔 Nouvelle demande de course</h2><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">Expire dans 18s</span></div>
        <div class="flex justify-between items-center flex-wrap gap-4">
          <div>
            <div class="flex justify-between gap-6"><span class="text-gray-500">📍 Prise en charge</span><strong>14 av. des Champs (1,2 km)</strong></div>
            <div class="flex justify-between gap-6 mt-1"><span class="text-gray-500">🏁 Destination</span><strong>Gare de Lyon</strong></div>
            <div class="flex justify-between gap-6 mt-1"><span class="text-gray-500">💰 Estimation</span><strong class="text-taxi-dark">19 400 FC · 18 min</strong></div>
          </div>
          <div class="flex gap-3">
            <button class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Refuser</button>
            <a href="{{ route('suivi') }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-green-600 text-white hover:bg-green-700 transition">Accepter ✓</a>
          </div>
        </div>
      </div>

      <!-- Revenus -->
      <div id="revenus" class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Revenus de la semaine</h3>
          <div class="flex items-end gap-2.5 h-40">
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:50%"></div>
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:70%"></div>
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:40%"></div>
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:85%"></div>
            <div class="flex-1 bg-taxi rounded-t-md" style="height:100%"></div>
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:60%"></div>
            <div class="flex-1 bg-gray-200 rounded-t-md" style="height:30%"></div>
          </div>
          <div class="flex justify-between mt-2 text-gray-500 text-xs"><span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span></div>
          <div class="h-px bg-gray-200 my-5"></div>
          <div class="flex justify-between"><span>Total semaine</span><strong class="text-xl">1 042 000 FC</strong></div>
        </div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Mes dernières courses</h3>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3 font-bold">Heure</th><th class="text-left px-4 py-3 font-bold">Trajet</th><th class="text-left px-4 py-3 font-bold">Gain</th></tr></thead>
              <tbody>
                <tr class="border-t border-gray-200"><td class="px-4 py-3">14:30</td><td class="px-4 py-3">Gare → Aéroport</td><td class="px-4 py-3"><strong>26 000 FC</strong></td></tr>
                <tr class="border-t border-gray-200"><td class="px-4 py-3">13:05</td><td class="px-4 py-3">Hôtel Lux → Centre</td><td class="px-4 py-3"><strong>9 600 FC</strong></td></tr>
                <tr class="border-t border-gray-200"><td class="px-4 py-3">11:48</td><td class="px-4 py-3">Bureau → Domicile</td><td class="px-4 py-3"><strong>15 000 FC</strong></td></tr>
                <tr class="border-t border-gray-200"><td class="px-4 py-3">10:20</td><td class="px-4 py-3">Gare → Hôpital</td><td class="px-4 py-3"><strong>12 300 FC</strong></td></tr>
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