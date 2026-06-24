@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Paiement course TG-'.$ride->id)

@section('content')
<section class="py-10">
  <div class="max-w-md mx-auto px-5">
    <a href="{{ route('rides.show', $ride) }}" class="text-taxi-dark font-semibold text-sm">← Retour à la course</a>

    <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 mt-4">
      <h1 class="text-2xl font-extrabold mb-1">Paiement Mobile Money</h1>
      <p class="text-gray-500 mb-6">Course #TG-{{ $ride->id }}</p>

      <div class="bg-gray-50 rounded-xl px-4 py-3 mb-6 space-y-2">
        <div class="flex justify-between items-center text-sm">
          <span class="text-gray-500">Prix de la course</span>
          <span class="font-semibold">@fc($breakdown['base'])</span>
        </div>
        @if ($breakdown['fee'] > 0)
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-500">Frais Labyrinthe ({{ rtrim(rtrim(number_format($breakdown['percent'], 2, ',', ' '), '0'), ',') }} %)</span>
            <span class="font-semibold">@fc($breakdown['fee'])</span>
          </div>
        @endif
        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
          <span class="text-gray-600 font-semibold">Total à payer</span>
          <strong class="text-2xl">@fc($breakdown['total'])</strong>
        </div>
      </div>

      @if (session('error'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-red-100 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-5 px-4 py-3 rounded-xl bg-red-100 text-red-800 text-sm">
          <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('rides.pay.store', $ride) }}">
        @csrf

        <div class="mb-4">
          <label for="method" class="block text-sm font-semibold mb-1.5">Opérateur</label>
          <select id="method" name="method" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
            <option value="mpesa" @selected(old('method') === 'mpesa')>M-Pesa (Vodacom)</option>
            <option value="airtel" @selected(old('method') === 'airtel')>Airtel Money</option>
            <option value="orange" @selected(old('method') === 'orange')>Orange Money</option>
          </select>
        </div>

        <div class="mb-6">
          <label for="phone" class="block text-sm font-semibold mb-1.5">Numéro Mobile Money</label>
          <input id="phone" name="phone" type="tel" value="{{ old('phone', auth()->user()->phone) }}" placeholder="0891234567"
                 class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
          <p class="text-xs text-gray-500 mt-1.5">Vous recevrez une demande de validation sur ce numéro.</p>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">
          Payer @fc($breakdown['total'])
        </button>
      </form>
    </div>
  </div>
</section>
@endsection
