@extends('mainPages.app', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Inscription')

@section('childContent')
<div class="min-h-screen grid lg:grid-cols-2">
    <aside class="hidden lg:flex flex-col justify-between p-16 bg-ink text-white" style="background-image:radial-gradient(circle at 30% 30%, rgba(255,206,0,0.25), transparent 50%)">
      @include('partials.brand-logo', ['class' => 'flex items-center gap-2.5 font-black text-2xl text-white'])
      <div>
        <h2 class="text-4xl font-black leading-tight">Rejoignez KinTaxiBooking<br>en moins d'une minute.</h2>
        <p class="text-gray-300 mt-4 max-w-sm">Créez votre compte gratuit et commandez votre première course dès maintenant.</p>
        <div class="flex gap-10 mt-8">
          <div><strong class="block text-3xl text-taxi">120k+</strong><span class="text-gray-400 text-sm">Utilisateurs</span></div>
          <div><div class="inline-flex items-center gap-1"><strong class="text-3xl text-taxi">4.9</strong><x-icon name="star-solid" class="w-6 h-6 text-taxi" /></div><span class="text-gray-400 text-sm">Satisfaction</span></div>
        </div>
      </div>
      <p class="text-gray-500 text-sm">© {{ date('Y') }} {{ config('app.name', 'KinTaxiBooking') }}</p>
    </aside>

    <main class="flex items-center justify-center p-5 sm:p-10">
      <form method="POST" action="{{ route('register.store') }}" class="w-full max-w-md bg-white rounded-2xl p-6 sm:p-10 shadow-lg2">
        @csrf
        <h1 class="text-3xl font-extrabold">Créer un compte</h1>
        <p class="text-sm text-gray-500 mb-4">Gratuit et sans engagement.</p>

        @if ($errors->any())
          <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-red-100 text-red-700">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Prénom</label><input name="firstname" value="{{ old('firstname') }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Jean" /></div>
          <div class="mb-4"><label class="block font-semibold mb-1.5 text-sm">Nom</label><input name="lastname" value="{{ old('lastname') }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Dupont" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Adresse e-mail</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="envelope" class="w-5 h-5" /></span><input type="email" name="email" value="{{ old('email') }}" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="vous@email.com" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Téléphone</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="device-phone-mobile" class="w-5 h-5" /></span><input type="tel" name="phone" value="{{ old('phone') }}" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="06 12 34 56 78" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Mot de passe</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="lock-closed" class="w-5 h-5" /></span><input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="8 caractères minimum" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Confirmer le mot de passe</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="lock-closed" class="w-5 h-5" /></span><input type="password" name="password_confirmation" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Retapez le mot de passe" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Type de compte</label>
          <select name="role" class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition"><option value="client" @selected(old('role', request('role')) === 'client')>Passager</option><option value="driver" @selected(old('role', request('role')) === 'driver')>Chauffeur partenaire</option></select>
        </div>
        <label class="flex items-center gap-2 text-sm mb-4"><input type="checkbox" required /> J'accepte les <a href="{{ route('legal.cgu') }}" class="text-taxi-dark">CGU</a> et la <a href="{{ route('legal.privacy') }}" class="text-taxi-dark">politique de confidentialité</a>.</label>
        <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Créer mon compte</button>

        <p class="text-center mt-6 text-sm text-gray-500">Déjà inscrit ? <a href="{{ route('login') }}" class="text-taxi-dark font-bold">Se connecter</a></p>
      </form>
    </main>
  </div>
@endsection