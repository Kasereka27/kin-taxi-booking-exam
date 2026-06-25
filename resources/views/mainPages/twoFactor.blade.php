@extends('root', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Vérification en deux étapes')

@section('childContent')
<div class="min-h-screen grid lg:grid-cols-2">
    <aside class="hidden lg:flex flex-col justify-between p-16 bg-ink text-white" style="background-image:radial-gradient(circle at 30% 30%, rgba(255,206,0,0.25), transparent 50%)">
      @include('partials.brand-logo', ['class' => 'flex items-center gap-2.5 font-black text-2xl text-white'])
      <div>
        <h2 class="text-4xl font-black leading-tight">Sécurisez votre accès.</h2>
        <p class="text-gray-300 mt-4 max-w-sm">Nous avons envoyé un code à votre adresse e-mail pour confirmer qu'il s'agit bien de vous.</p>
      </div>
      <p class="text-gray-500 text-sm">© {{ date('Y') }} {{ config('app.name', 'KinTaxiBooking') }}</p>
    </aside>

    <main class="flex items-center justify-center p-10">
      <div class="w-full max-w-md bg-white rounded-2xl p-10 shadow-lg2">
        <h1 class="text-3xl font-extrabold">Code de vérification</h1>
        <p class="text-sm text-gray-500 mb-4">Saisissez le code à 6 chiffres envoyé à <strong>{{ $email }}</strong>.</p>

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

        <form method="POST" action="{{ route('two-factor.store') }}" class="space-y-4">
          @csrf
          <div>
            <label class="block font-semibold mb-1.5 text-sm">Code OTP</label>
            <input
              type="text"
              name="code"
              value="{{ old('code') }}"
              inputmode="numeric"
              pattern="[0-9]*"
              maxlength="{{ config('two_factor.code_length') }}"
              autocomplete="one-time-code"
              required
              class="w-full px-4 py-3 rounded-lg border border-gray-300 text-center text-2xl tracking-[0.4em] font-bold focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition"
              placeholder="000000"
            />
          </div>
          <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Valider</button>
        </form>

        <div class="flex flex-col sm:flex-row gap-3 mt-5">
          <form method="POST" action="{{ route('two-factor.resend') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-full font-semibold border-2 border-gray-300 hover:border-ink transition text-sm">Renvoyer le code</button>
          </form>
          <form method="POST" action="{{ route('two-factor.cancel') }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 rounded-full font-semibold text-gray-500 hover:text-ink transition text-sm">Annuler</button>
          </form>
        </div>

        <p class="text-center mt-6 text-xs text-gray-400">Le code expire après {{ config('two_factor.expires_minutes') }} minutes.</p>
      </div>
    </main>
  </div>
@endsection
