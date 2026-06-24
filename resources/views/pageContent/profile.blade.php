@extends('mainPages.app')

@section('title', 'Mon profil')

@section('content')
<section class="py-12 bg-ink text-white text-center">
  <div class="max-w-6xl mx-auto px-5">
    <span class="inline-block uppercase tracking-widest text-xs font-bold text-taxi-dark bg-taxi/15 px-3.5 py-1.5 rounded-full">Mon compte</span>
    <h1 class="mt-4 text-3xl font-extrabold">Profil utilisateur</h1>
    <p class="mt-2 text-gray-300">Gérez vos informations personnelles et votre mot de passe.</p>
  </div>
</section>

<section class="py-16">
  <div class="max-w-3xl mx-auto px-5 space-y-6">
    @if (session('success'))
      <div class="px-4 py-3 rounded-xl text-sm bg-green-100 text-green-800 font-semibold">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-full bg-taxi grid place-items-center font-extrabold text-2xl text-ink">
          {{ strtoupper(substr($user->firstname, 0, 1).substr($user->lastname, 0, 1)) }}
        </div>
        <div>
          <h2 class="text-xl font-bold">{{ $user->firstname }} {{ $user->lastname }}</h2>
          <p class="text-gray-500 text-sm capitalize">{{ $user->role }} · Membre depuis {{ $user->created_at?->translatedFormat('F Y') }}</p>
          @if ($user->google_id)
            <p class="text-sm text-blue-600 mt-1">Compte lié à Google</p>
          @endif
        </div>
      </div>

      <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <h3 class="font-bold text-lg">Informations personnelles</h3>

        @if ($errors->any() && ! $errors->has('current_password') && ! $errors->has('password'))
          <div class="px-4 py-3 rounded-lg text-sm bg-red-100 text-red-700">
            <ul class="list-disc list-inside space-y-1">
              @foreach ($errors->get('firstname') as $error)<li>{{ $error }}</li>@endforeach
              @foreach ($errors->get('lastname') as $error)<li>{{ $error }}</li>@endforeach
              @foreach ($errors->get('email') as $error)<li>{{ $error }}</li>@endforeach
              @foreach ($errors->get('phone') as $error)<li>{{ $error }}</li>@endforeach
            </ul>
          </div>
        @endif

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1.5 text-sm">Prénom</label>
            <input name="firstname" value="{{ old('firstname', $user->firstname) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
          </div>
          <div>
            <label class="block font-semibold mb-1.5 text-sm">Nom</label>
            <input name="lastname" value="{{ old('lastname', $user->lastname) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
          </div>
        </div>
        <div>
          <label class="block font-semibold mb-1.5 text-sm">E-mail</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
        </div>
        <div>
          <label class="block font-semibold mb-1.5 text-sm">Téléphone</label>
          <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="+243 900 000 000" />
        </div>
        <button type="submit" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Enregistrer</button>
      </form>
    </div>

    @if ($driverProfile)
      <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
        <h3 class="font-bold text-lg mb-4">Profil chauffeur</h3>
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
          <div><span class="text-gray-500">Véhicule</span><div class="font-semibold">{{ $driverProfile->vehicle_model }}</div></div>
          <div><span class="text-gray-500">Plaque</span><div class="font-semibold">{{ $driverProfile->plate }}</div></div>
          <div><span class="text-gray-500">Type</span><div class="font-semibold capitalize">{{ $driverProfile->vehicle_type }}</div></div>
          <div><span class="text-gray-500">Statut</span><div class="font-semibold capitalize">{{ $driverProfile->approval_status }}</div></div>
        </div>
        <p class="text-sm text-gray-500 mt-4">Pour modifier votre véhicule ou documents, contactez le support.</p>
      </div>
    @endif

    <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200">
      <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
        @csrf
        @method('PATCH')
        <h3 class="font-bold text-lg">Mot de passe</h3>

        @if ($errors->has('current_password') || $errors->has('password'))
          <div class="px-4 py-3 rounded-lg text-sm bg-red-100 text-red-700">
            <ul class="list-disc list-inside space-y-1">
              @foreach (['current_password', 'password'] as $field)
                @foreach ($errors->get($field) as $error)
                  <li>{{ $error }}</li>
                @endforeach
              @endforeach
            </ul>
          </div>
        @endif

        @if ($user->google_id)
          <p class="text-sm text-gray-500">Compte Google : utilisez « Mot de passe oublié » sur la page de connexion pour définir un mot de passe local.</p>
        @endif

        <div>
          <label class="block font-semibold mb-1.5 text-sm">Mot de passe actuel</label>
          <input type="password" name="current_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block font-semibold mb-1.5 text-sm">Nouveau mot de passe</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
          </div>
          <div>
            <label class="block font-semibold mb-1.5 text-sm">Confirmation</label>
            <input type="password" name="password_confirmation" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
          </div>
        </div>
        <button type="submit" class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Changer le mot de passe</button>
      </form>
    </div>

    <div class="text-center">
      <a href="{{ route($user->dashboardRouteName()) }}" class="text-taxi-dark font-semibold">← Retour à mon espace</a>
    </div>
  </div>
</section>
@endsection
