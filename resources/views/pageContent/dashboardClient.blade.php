@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Dashboard Client')

@section('header')
@show

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    <!-- Sidebar -->
    <aside class="bg-ink text-gray-300 p-5 flex lg:flex-col gap-1 overflow-x-auto">
      <a href="index.html" class="hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2"><span class="w-9 h-9 rounded-xl bg-taxi grid place-items-center shadow-taxi text-xl">🚕</span> Taxi<span class="text-taxi-dark">Go</span></a>
      <nav class="flex lg:flex-col gap-1 flex-1">
        <a href="dashboard-client.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap bg-taxi text-ink">📊 <span class="hidden lg:inline">Tableau de bord</span></a>
        <a href="reservation.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">➕ <span class="hidden lg:inline">Nouvelle course</span></a>
        <a href="suivi.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">📡 <span class="hidden lg:inline">Suivi en direct</span></a>
        <a href="historique.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🕓 <span class="hidden lg:inline">Historique</span></a>
        <a href="paiement.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">💳 <span class="hidden lg:inline">Paiement</span></a>
        <a href="profil.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">👤 <span class="hidden lg:inline">Profil</span></a>
        <a href="contact.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap hover:bg-gray-800 hover:text-white transition">🛟 <span class="hidden lg:inline">Aide</span></a>
      </nav>
      <div class="hidden lg:block border-t border-gray-800 pt-4 mt-4">
        <a href="connexion.html" class="flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition">🚪 Déconnexion</a>
      </div>
    </aside>

    <!-- Main -->
    <main class="p-7 lg:px-9">
      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div>
          <h1 class="text-2xl font-extrabold">Bonjour, Jean 👋</h1>
          <p class="text-gray-500">Voici un aperçu de votre activité.</p>
        </div>
        <div class="flex gap-3 items-center">
          <a href="reservation.html" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">+ Réserver</a>
          <div class="w-10 h-10 rounded-full bg-taxi grid place-items-center font-extrabold text-ink">JD</div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Courses totales</div><div class="text-3xl font-extrabold mt-1">48</div><div class="text-green-600 text-sm font-semibold mt-1.5">▲ 12% ce mois</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Dépenses du mois</div><div class="text-3xl font-extrabold mt-1">214 €</div><div class="text-green-600 text-sm font-semibold mt-1.5">▲ 8%</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Distance parcourue</div><div class="text-3xl font-extrabold mt-1">612 km</div><div class="text-gray-500 text-sm mt-1.5">cette année</div></div>
        <div class="bg-white rounded-xl p-5 shadow-xs border border-gray-200"><div class="text-gray-500 text-sm">Note moyenne donnée</div><div class="text-3xl font-extrabold mt-1">4.8★</div><div class="text-gray-500 text-sm mt-1.5">Merci !</div></div>
      </div>

      <!-- Course en cours -->
      <div class="bg-white rounded-2xl p-6 shadow-soft border border-gray-200 border-l-4 border-l-taxi mb-6">
        <div class="flex justify-between items-center flex-wrap gap-3">
          <div class="flex gap-3 items-center">
            <span class="w-2 h-2 rounded-full bg-green-500 ring-4 ring-green-100 animate-pulse"></span>
            <div>
              <strong>Course en cours · #TG-1042</strong>
              <div class="text-gray-500 text-sm">Gare Centrale → Aéroport T2 · Marc D.</div>
            </div>
          </div>
          <a href="suivi.html" class="inline-flex px-4 py-2 rounded-full font-bold text-sm bg-ink text-white hover:bg-ink-soft transition">Suivre en direct →</a>
        </div>
      </div>

      <!-- Historique -->
      <div class="flex justify-between items-center mb-4"><h2 class="text-xl font-bold">Courses récentes</h2><a href="historique.html" class="text-taxi-dark font-semibold">Tout voir</a></div>
      <div class="overflow-x-auto rounded-xl border border-gray-200">
        <table class="w-full bg-white">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
            <th class="text-left px-4 py-3.5 font-bold">Réf.</th><th class="text-left px-4 py-3.5 font-bold">Date</th><th class="text-left px-4 py-3.5 font-bold">Trajet</th><th class="text-left px-4 py-3.5 font-bold">Chauffeur</th><th class="text-left px-4 py-3.5 font-bold">Montant</th><th class="text-left px-4 py-3.5 font-bold">Statut</th>
          </tr></thead>
          <tbody id="rides-body"></tbody>
        </table>
      </div>
    </main>
  </div>
@endsection

@section('footer')
<script>
    const body = document.getElementById("rides-body");
    body.innerHTML = DEMO.rides.map(r => {
      const cls = r.status === "Terminée" ? "bg-green-100 text-green-700" : r.status === "Annulée" ? "bg-red-100 text-red-700" : "bg-yellow-100 text-yellow-700";
      return `<tr class="border-t border-gray-200 hover:bg-gray-50"><td class="px-4 py-3.5"><strong>${r.id}</strong></td><td class="px-4 py-3.5">${r.date}</td><td class="px-4 py-3.5">${r.from} → ${r.to}</td><td class="px-4 py-3.5">${r.driver}</td><td class="px-4 py-3.5">${r.price}</td><td class="px-4 py-3.5"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold ${cls}">${r.status}</span></td></tr>`;
    }).join("");
  </script>
@endsection