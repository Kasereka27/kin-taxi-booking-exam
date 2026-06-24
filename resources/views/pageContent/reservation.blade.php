@extends('mainPages.app')

@section('title', 'Réservation')

@section('content')
<section class="py-12 bg-ink text-white">
    <div class="max-w-6xl mx-auto px-5">
      <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Réservation</span>
      <h1 class="mt-4 text-3xl font-extrabold">Commandez votre course</h1>
      <p class="text-gray-400">Remplissez les détails ci-dessous, choisissez votre véhicule et c'est parti.</p>
    </div>
  </section>

  <section class="py-12">
    <div class="max-w-6xl mx-auto px-5 grid lg:grid-cols-[1.4fr_1fr] gap-6 items-start">
      <!-- Formulaire -->
      <form method="POST" action="{{ route('rides.store') }}" class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
        @csrf
        <h3 class="text-lg font-bold mb-4">Détails du trajet</h3>

        @if ($errors->any())
          <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-red-100 text-red-700">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Adresse de départ</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">📍</span><input id="pickup" name="pickup_addr" value="{{ old('pickup_addr', request('pickup_addr')) }}" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Ex : 12 av. de la Libération, Kinshasa" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Adresse de destination</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400">🏁</span><input id="dropoff" name="dropoff_addr" value="{{ old('dropoff_addr', request('dropoff_addr')) }}" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Ex : Aéroport de N'djili" /></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Date</label><input type="date" name="date" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" /></div>
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Heure</label><input type="time" name="time" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" /></div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Passagers</label><select name="passengers" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition"><option>1</option><option>2</option><option>3</option><option>4</option><option>5+</option></select></div>
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Bagages</label><select name="luggage" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition"><option>Aucun</option><option>1-2</option><option>3+</option></select></div>
        </div>

        <label class="block font-semibold mb-2">Type de véhicule</label>
        <input type="hidden" id="vehicleType" name="vehicleType" value="eco" />
        <div class="grid grid-cols-3 gap-3.5 mb-4">
          <div class="car-option border-2 border-taxi bg-yellow-50 ring-2 ring-taxi rounded-xl p-4 text-center cursor-pointer transition" data-type="eco"><div class="text-3xl">🚗</div><div>Éco</div><div class="font-bold">dès 5 000 FC</div></div>
          <div class="car-option border-2 border-gray-200 rounded-xl p-4 text-center cursor-pointer hover:border-taxi transition" data-type="confort"><div class="text-3xl">🚙</div><div>Confort</div><div class="font-bold">dès 8 000 FC</div></div>
          <div class="car-option border-2 border-gray-200 rounded-xl p-4 text-center cursor-pointer hover:border-taxi transition" data-type="van"><div class="text-3xl">🚐</div><div>Van</div><div class="font-bold">dès 12 000 FC</div></div>
        </div>

        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Mode de paiement</label>
          <select name="payment" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition"><option>Carte enregistrée •••• 4242</option><option>Espèces</option><option>Paiement mobile</option></select>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Note pour le chauffeur (optionnel)</label>
          <textarea name="note" rows="2" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Ex : 3e étage, sonner à l'interphone"></textarea>
        </div>

        <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Rechercher un chauffeur 🔍</button>
      </form>

      <!-- Récap -->
      <aside class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 lg:sticky lg:top-24">
        <h3 class="text-lg font-bold mb-4">Estimation</h3>
        <div id="estimate" class="flex flex-col gap-2.5"></div>
        <div class="h-px bg-gray-200 my-5"></div>
        <div class="bg-blue-100 text-blue-700 px-4 py-3 rounded-lg text-sm mb-4">💡 Prix indicatif. Le tarif final peut varier selon le trafic et l'itinéraire réel.</div>
        <ul class="space-y-2">
          <li class="flex gap-2.5"><span class="text-green-500 font-bold">✓</span> Annulation gratuite jusqu'à 5 min</li>
          <li class="flex gap-2.5"><span class="text-green-500 font-bold">✓</span> Suivi GPS en temps réel</li>
          <li class="flex gap-2.5"><span class="text-green-500 font-bold">✓</span> Chauffeur vérifié et noté</li>
        </ul>
      </aside>
    </div>
  </section>
@endsection


