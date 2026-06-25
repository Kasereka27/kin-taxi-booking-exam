@extends('root', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Vérification e-mail')

@section('childContent')
<div class="min-h-screen flex items-center justify-center p-10 bg-gray-50">
  <div class="w-full max-w-md bg-white rounded-2xl p-10 shadow-lg2 text-center">
    <div class="text-5xl mb-4">✉️</div>
    <h1 class="text-3xl font-extrabold mb-3">Confirmez votre e-mail</h1>
    <p class="text-gray-500 text-sm mb-6">
      Un lien de confirmation a été envoyé à <strong>{{ auth()->user()->email }}</strong>.
      Cliquez dessus pour activer toutes les fonctionnalités de votre compte.
    </p>

    @if (session('status'))
      <div class="mb-4 px-4 py-3 rounded-lg text-sm bg-green-100 text-green-700 font-medium">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
      @csrf
      <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">
        Renvoyer l'e-mail
      </button>
    </form>

    <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="text-taxi-dark font-semibold text-sm">Continuer vers mon espace →</a>
  </div>
</div>
@endsection
