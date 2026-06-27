@extends('mainPages.dashboard')

@section('title', 'Courses en cours')

@section('header')
@show

@php
    $statusStyles = [
        'pending' => 'bg-gray-100 text-gray-700',
        'assigned' => 'bg-blue-100 text-blue-700',
        'approche' => 'bg-blue-100 text-blue-700',
        'course' => 'bg-yellow-100 text-yellow-700',
    ];
    $liveStatusLabels = array_intersect_key($statusLabels, array_flip(\App\Models\Ride::liveStatuses()));
@endphp

@section('content')
<x-dashboard-shell>
  <x-slot name="sidebar">
    @include('partials.admin-sidebar', ['activePage' => 'live-rides'])
  </x-slot>

      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div>
          <h1 class="text-2xl font-extrabold">Courses en cours</h1>
          <p class="text-gray-500">Suivi en temps réel des courses actives sur la plateforme.</p>
        </div>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
          Live
        </span>
      </div>

      <form method="GET" action="{{ route('admin.live-rides') }}" class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-semibold mb-1.5" for="search">Recherche</label>
          <input id="search" name="search" value="{{ $search }}" placeholder="Client, chauffeur ou adresse…" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-semibold mb-1.5" for="status">Statut</label>
          <select id="status" name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
            <option value="">Tous les statuts actifs</option>
            @foreach ($liveStatusLabels as $value => $label)
              <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-lg font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Filtrer</button>
        <a href="{{ route('admin.live-rides') }}" class="px-5 py-2.5 rounded-lg font-bold border-2 border-gray-300 hover:border-ink transition">Réinitialiser</a>
      </form>

      <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
        <table class="w-full bg-white">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3.5 font-bold">Réf.</th><th class="text-left px-4 py-3.5 font-bold">Client</th><th class="text-left px-4 py-3.5 font-bold">Chauffeur</th><th class="text-left px-4 py-3.5 font-bold">Trajet</th><th class="text-left px-4 py-3.5 font-bold">Montant</th><th class="text-left px-4 py-3.5 font-bold">Statut</th><th class="text-left px-4 py-3.5 font-bold">Demandée le</th><th class="text-left px-4 py-3.5 font-bold">Action</th></tr></thead>
          <tbody>
            @forelse ($rides as $ride)
            <tr class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-4 py-3.5"><strong>{{ $ride->reference() }}</strong></td>
              <td class="px-4 py-3.5">{{ $ride->client?->firstname }} {{ Str::limit($ride->client?->lastname, 1, '.') }}</td>
              <td class="px-4 py-3.5">{{ $ride->driver ? $ride->driver->firstname.' '.Str::limit($ride->driver->lastname, 1, '.') : '—' }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ Str::before($ride->pickup_addr, ',') }} → {{ Str::before($ride->dropoff_addr, ',') }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap">@fc($ride->price)</td>
              <td class="px-4 py-3.5"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $statusStyles[$ride->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $ride->statusLabel() }}</span></td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ $ride->requested_at?->translatedFormat('d M Y H:i') ?? '—' }}</td>
              <td class="px-4 py-3.5"><a href="{{ route('rides.show', $ride) }}" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold border-2 border-gray-300 hover:border-ink transition">Voir</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Aucune course en cours pour le moment.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $rides->links() }}
</x-dashboard-shell>
@endsection

@section('footer')
@endsection
