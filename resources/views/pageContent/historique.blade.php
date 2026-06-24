@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Historique des courses')

@section('content')
<section class="py-10">
  <div class="max-w-6xl mx-auto px-5">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
      <div>
        <h1 class="text-2xl font-extrabold">Historique des courses</h1>
        <p class="text-gray-500">Retrouvez et filtrez toutes vos courses.</p>
      </div>
      <a href="{{ route('reservation') }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">+ Réserver</a>
    </div>

    @if (session('success'))
      <div class="mb-6 px-4 py-3 rounded-lg text-sm bg-green-100 text-green-700 font-medium">{{ session('success') }}</div>
    @endif

    <!-- Filtres -->
    <form method="GET" action="{{ route('rides.index') }}" class="bg-white rounded-xl p-4 shadow-xs border border-gray-200 mb-6 flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="block font-semibold mb-1.5 text-sm">Recherche (adresse)</label>
        <input type="text" name="search" value="{{ $filters['search'] }}" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition" placeholder="Départ ou destination…" />
      </div>
      <div class="min-w-[180px]">
        <label class="block font-semibold mb-1.5 text-sm">Statut</label>
        <select name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 bg-white focus:outline-hidden focus:border-taxi focus:ring-2 focus:ring-taxi/30 transition">
          <option value="">Tous les statuts</option>
          @foreach ($statusLabels as $value => $label)
            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="inline-flex px-5 py-2.5 rounded-lg font-bold bg-ink text-white hover:bg-ink-soft transition">Filtrer</button>
      <a href="{{ route('rides.index') }}" class="inline-flex px-5 py-2.5 rounded-lg font-bold border border-gray-300 hover:bg-gray-100 transition">Réinitialiser</a>
    </form>

    <!-- Tableau -->
    <div class="overflow-x-auto rounded-xl border border-gray-200">
      <table class="w-full bg-white">
        <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase tracking-wide">
          <th class="text-left px-4 py-3.5 font-bold">Réf.</th>
          <th class="text-left px-4 py-3.5 font-bold">Date</th>
          <th class="text-left px-4 py-3.5 font-bold">Trajet</th>
          <th class="text-left px-4 py-3.5 font-bold">Véhicule</th>
          <th class="text-left px-4 py-3.5 font-bold">Montant</th>
          <th class="text-left px-4 py-3.5 font-bold">Statut</th>
          <th class="text-left px-4 py-3.5 font-bold">Actions</th>
        </tr></thead>
        <tbody>
          @forelse ($rides as $ride)
            <tr class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-4 py-3.5"><strong>{{ $ride->reference() }}</strong></td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ $ride->created_at->format('d/m/Y H:i') }}</td>
              <td class="px-4 py-3.5">{{ $ride->pickup_addr }} → {{ $ride->dropoff_addr }}</td>
              <td class="px-4 py-3.5 capitalize">{{ $ride->vehicle_type }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap">@fc($ride->price)</td>
              <td class="px-4 py-3.5">
                @php($cls = match ($ride->status) { 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', 'pending' => 'bg-gray-100 text-gray-700', default => 'bg-yellow-100 text-yellow-700' })
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $cls }}">{{ $ride->statusLabel() }}</span>
              </td>
              <td class="px-4 py-3.5">
                <div class="flex gap-2">
                  <a href="{{ route('rides.show', $ride) }}" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold border-2 border-gray-300 hover:border-ink transition">Voir</a>
                  @can('update', $ride)
                    @if ($ride->status === 'pending')
                      <form method="POST" action="{{ route('rides.cancel', $ride) }}" onsubmit="return confirm('Annuler cette course ?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-600 hover:bg-red-100 transition">Annuler</button>
                      </form>
                    @endif
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-500">Aucune course ne correspond à votre recherche.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      {{ $rides->links() }}
    </div>
  </div>
</section>
@endsection
