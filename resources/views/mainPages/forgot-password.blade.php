@extends('root', ['cssClass' => 'font-sans text-ink'])

@section('title', 'Mot de passe oublié')

@section('childContent')
<div class="min-h-screen flex items-center justify-center p-10 bg-gray-50">
  <form method="POST" action="{{ route('password.email') }}" class="w-full max-w-md bg-white rounded-2xl p-10 shadow-lg2">
    @csrf
    <h1 class="text-3xl font-extrabold">Mot de passe oublié</h1>
    <p class="text-sm text-gray-500 mt-2 mb-6">Indiquez votre e-mail pour recevoir un lien de réinitialisation.</p>

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
      <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="vous@email.com" />
    </div>

    <button class="w-full inline-flex items-center justify-center px-6 py-4 rounded-full font-bold text-lg bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Envoyer le lien</button>
    <p class="text-center mt-6 text-sm text-gray-500"><a href="{{ route('login') }}" class="text-taxi-dark font-bold">← Retour à la connexion</a></p>
  </form>
</div>
@endsection
