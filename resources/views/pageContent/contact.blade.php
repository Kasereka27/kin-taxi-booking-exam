@extends('mainPages.app')

@section('title', 'Contact')

@section('content')
<section class="py-12 bg-ink text-white text-center">
    <div class="max-w-6xl mx-auto px-5"><span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Nous contacter</span><h1 class="mt-4 text-3xl font-extrabold">Une question ? On est là 24/7</h1></div>
  </section>

  <section class="py-20">
    <div class="max-w-6xl mx-auto px-5 grid lg:grid-cols-2 gap-6 items-start">
      <form method="POST" action="{{ route('contact.store') }}" class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
        @csrf
        <h3 class="text-lg font-bold mb-4">Envoyez-nous un message</h3>

        @if (session('success'))
          <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-green-100 text-green-700 font-medium">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
          <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-red-100 text-red-700">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Nom</label><input name="name" value="{{ old('name') }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" /></div>
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">E-mail</label><input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Sujet</label>
          <select name="subject" required class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi transition">
            <option value="general" @selected(old('subject') === 'general')>Question générale</option>
            <option value="ride" @selected(old('subject') === 'ride')>Problème avec une course</option>
            <option value="billing" @selected(old('subject') === 'billing')>Facturation</option>
            <option value="driver" @selected(old('subject') === 'driver')>Devenir chauffeur</option>
            <option value="partnership" @selected(old('subject') === 'partnership')>Partenariat</option>
          </select>
        </div>
        <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Message</label><textarea name="message" rows="5" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition">{{ old('message') }}</textarea></div>
        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Envoyer le message</button>
      </form>

      <div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 mb-6">
          <h3 class="text-lg font-bold mb-4">Coordonnées</h3>
          <div class="flex gap-3 mb-4 items-center"><span class="text-2xl">📞</span><div><strong>+243 900 000 000</strong><div class="text-gray-500 text-sm">Support 24h/24</div></div></div>
          <div class="flex gap-3 mb-4 items-center"><span class="text-2xl">✉️</span><div><strong>support@kintaxibooking.com</strong><div class="text-gray-500 text-sm">Réponse sous 24h</div></div></div>
          <div class="flex gap-3 items-center"><span class="text-2xl">📍</span><div><strong>Kinshasa, RDC</strong><div class="text-gray-500 text-sm">République Démocratique du Congo</div></div></div>
        </div>
        <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
          <h3 class="text-lg font-bold mb-4">Questions fréquentes</h3>
          <details class="py-2.5 border-b border-gray-100"><summary class="cursor-pointer font-semibold">Comment annuler une course ?</summary><p class="text-gray-500 mt-2">Depuis l'écran de suivi, cliquez sur « Annuler la course ». Gratuit dans les 5 premières minutes.</p></details>
          <details class="py-2.5 border-b border-gray-100"><summary class="cursor-pointer font-semibold">Quels moyens de paiement ?</summary><p class="text-gray-500 mt-2">Mobile Money (M-Pesa, Airtel, Orange) via Labyrinthe, en francs congolais.</p></details>
          <details class="py-2.5 border-b border-gray-100"><summary class="cursor-pointer font-semibold">Comment devenir chauffeur ?</summary><p class="text-gray-500 mt-2">Inscrivez-vous en choisissant « Chauffeur partenaire » lors de la création de compte.</p></details>
          <details class="py-2.5"><summary class="cursor-pointer font-semibold">Le suivi est-il en temps réel ?</summary><p class="text-gray-500 mt-2">Oui, la position du chauffeur est mise à jour en continu sur la carte pendant votre course active.</p></details>
        </div>
      </div>
    </div>
  </section>
@endsection
