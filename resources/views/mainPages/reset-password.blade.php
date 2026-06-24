@extends('root', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Réinitialiser le mot de passe')

@section('childContent')
<div class="min-h-screen flex items-center justify-center p-10 bg-gray-50">
  <form method="POST" action="{{ route('password.update') }}" class="w-full max-w-md bg-white rounded-2xl p-10 shadow-lg2">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}" />
    <h1 class="text-3xl font-extrabold">Nouveau mot de passe</h1>
    <p class="text-sm text-gray-500 mt-2 mb-6">Choisissez un mot de passe sécurisé pour votre compte.</p>

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
      <input type="email" name="email" value="{{ old('email', $email) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
    </div>
    <div class="mb-4">
      <label class="block font-semibold mb-1.5 text-sm">Nouveau mot de passe</label>
      <input type="password" name="password" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
    </div>
    <div class="mb-4">
      <label class="block font-semibold mb-1.5 text-sm">Confirmation</label>
      <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" />
    </div>

    <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réinitialiser</button>
  </form>
</div>
@endsection
