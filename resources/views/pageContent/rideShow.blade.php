@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Course TG-'.$ride->id)

@section('content')
<section class="py-10">
  <div class="max-w-3xl mx-auto px-5">
    <a href="{{ route('rides.index') }}" class="text-taxi-dark font-semibold text-sm">← Retour à l'historique</a>

    <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 mt-4">
      <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <h1 class="text-2xl font-extrabold">Course #TG-{{ $ride->id }}</h1>
        @php($cls = match ($ride->status) { 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', 'pending' => 'bg-gray-100 text-gray-700', default => 'bg-yellow-100 text-yellow-700' })
        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ $ride->statusLabel() }}</span>
      </div>

      <div class="grid sm:grid-cols-2 gap-5">
        <div>
          <div class="text-gray-500 text-sm">📍 Départ</div>
          <div class="font-semibold">{{ $ride->pickup_addr }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">🏁 Destination</div>
          <div class="font-semibold">{{ $ride->dropoff_addr }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Véhicule</div>
          <div class="font-semibold capitalize">{{ $ride->vehicle_type }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Distance estimée</div>
          <div class="font-semibold">{{ $ride->distance_km ? number_format((float) $ride->distance_km, 2, ',', ' ').' km' : '—' }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Montant</div>
          <div class="font-semibold">@fc($ride->price)</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Chauffeur</div>
          <div class="font-semibold">{{ $ride->driver ? $ride->driver->firstname.' '.$ride->driver->lastname : 'Non assigné' }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Demandée le</div>
          <div class="font-semibold">{{ optional($ride->requested_at ?? $ride->created_at)->format('d/m/Y H:i') }}</div>
        </div>
        <div>
          <div class="text-gray-500 text-sm">Terminée le</div>
          <div class="font-semibold">{{ $ride->completed_at ? $ride->completed_at->format('d/m/Y H:i') : '—' }}</div>
        </div>
      </div>

      @if ($ride->status === 'completed')
        <div class="mt-7 pt-6 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
          @if ($ride->isPaid())
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">✅ Course payée</span>
          @else
            <span class="text-gray-500 text-sm">Cette course n'est pas encore réglée.</span>
            @can('pay', $ride)
              <a href="{{ route('rides.pay', $ride) }}" class="inline-flex px-5 py-2.5 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Payer @fc($ride->price)</a>
            @endcan
          @endif
        </div>
      @endif

      @can('update', $ride)
        @if ($ride->status === 'pending')
          <div class="flex gap-3 mt-7 pt-6 border-t border-gray-200">
            <form method="POST" action="{{ route('rides.cancel', $ride) }}" onsubmit="return confirm('Annuler cette course ?');">
              @csrf
              @method('PATCH')
              <button type="submit" class="inline-flex px-5 py-2.5 rounded-full font-bold bg-red-50 text-red-600 hover:bg-red-100 transition">Annuler la course</button>
            </form>
            <form method="POST" action="{{ route('rides.destroy', $ride) }}" onsubmit="return confirm('Supprimer définitivement cette course ?');">
              @csrf
              @method('DELETE')
              <button type="submit" class="inline-flex px-5 py-2.5 rounded-full font-bold border border-gray-300 hover:bg-gray-100 transition">Supprimer</button>
            </form>
          </div>
        @endif
      @endcan
    </div>
  </div>
</section>
@endsection
