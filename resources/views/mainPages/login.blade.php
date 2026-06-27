@extends('root', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Connexion')

@section('childContent')
<div class="min-h-screen grid lg:grid-cols-2">
    <aside class="hidden lg:flex flex-col justify-between p-16 bg-ink text-white" style="background-image:radial-gradient(circle at 30% 30%, rgba(255,206,0,0.25), transparent 50%)">
      @include('partials.brand-logo', ['class' => 'flex items-center gap-2.5 font-black text-2xl text-white'])
      <div>
        <h2 class="text-4xl font-black leading-tight">Bon retour parmi nous.</h2>
        <p class="text-gray-300 mt-4 max-w-sm">Connectez-vous pour réserver une course, suivre votre chauffeur et consulter votre historique.</p>
        <ul class="mt-8 space-y-2 text-gray-300">
          <li class="flex gap-2.5 items-start"><x-icon name="check" class="w-5 h-5 text-green-400 shrink-0" /> Suivi en temps réel de vos trajets</li>
          <li class="flex gap-2.5 items-start"><x-icon name="check" class="w-5 h-5 text-green-400 shrink-0" /> Historique et reçus en un clic</li>
          <li class="flex gap-2.5 items-start"><x-icon name="check" class="w-5 h-5 text-green-400 shrink-0" /> Paiement sécurisé enregistré</li>
        </ul>
      </div>
      <p class="text-gray-500 text-sm">© {{ date('Y') }} {{ config('app.name', 'KinTaxiBooking') }}</p>
    </aside>

    <main class="flex items-center justify-center p-5 sm:p-10">
      <form method="POST" action="{{ route('login.store') }}" class="w-full max-w-md bg-white rounded-2xl p-6 sm:p-10 shadow-lg2">
        @csrf
        <h1 class="text-3xl font-extrabold">Connexion</h1>
        <p class="text-sm text-gray-500 mb-4">Accédez à votre espace {{ config('app.name', 'KinTaxiBooking') }}.</p>

        @if (session('status'))
          <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-green-100 text-green-700 font-medium">{{ session('status') }}</div>
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

        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Adresse e-mail</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="envelope" class="w-5 h-5" /></span><input type="email" name="email" value="{{ old('email') }}" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="vous@email.com" /></div>
        </div>
        <div class="mb-4">
          <label class="block font-semibold mb-1.5 text-sm">Mot de passe</label>
          <div class="relative"><span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"><x-icon name="lock-closed" class="w-5 h-5" /></span><input type="password" name="password" required class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="••••••••" /></div>
        </div>
        <div class="flex justify-between items-center mb-4">
          <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" /> Se souvenir de moi</label>
          <a href="{{ route('password.request') }}" class="text-taxi-dark font-semibold text-sm">Mot de passe oublié ?</a>
        </div>
        <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Se connecter</button>

        @if (config('services.google.client_id'))
          <div class="flex items-center gap-3 text-gray-400 text-sm my-5"><div class="flex-1 h-px bg-gray-200"></div>ou<div class="flex-1 h-px bg-gray-200"></div></div>
          <a href="{{ route('auth.google.redirect') }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Continuer avec Google</a>
        @endif

        <p class="text-center mt-6 text-sm text-gray-500">Pas encore de compte ? <a href="{{ route('register') }}" class="text-taxi-dark font-bold">Créer un compte</a></p>
      </form>
    </main>
  </div>
@endsection