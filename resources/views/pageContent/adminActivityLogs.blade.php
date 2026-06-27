@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Journal d\'activité')

@section('header')
@show

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    <aside class="bg-ink text-gray-300 p-5 flex lg:flex-col gap-1 overflow-x-auto">
      @include('partials.brand-logo', ['class' => 'hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2'])
      <nav class="flex lg:flex-col gap-1 flex-1">
        <x-dashboard-nav-link :href="route('admin.dashboard')" icon="chart-bar" label="Vue d'ensemble" />
        <x-dashboard-nav-link :href="route('admin.users')" icon="users" label="Utilisateurs" />
        <x-dashboard-nav-link :href="route('admin.activity-logs')" icon="clipboard-document-list" label="Journal d'activité" :active="true" />
      </nav>
      <div class="hidden lg:block border-t border-gray-800 pt-4 mt-4">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition"><x-icon name="arrow-right-on-rectangle" class="w-5 h-5 shrink-0" /> Déconnexion</button>
        </form>
      </div>
    </aside>

    <main class="p-4 sm:p-7 lg:px-9">
      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div>
          <h1 class="text-2xl font-extrabold">Journal d'activité</h1>
          <p class="text-gray-500">Historique des actions importantes sur la plateforme.</p>
        </div>
      </div>

      <form method="GET" action="{{ route('admin.activity-logs') }}" class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-semibold mb-1.5" for="search">Recherche</label>
          <input id="search" name="search" value="{{ $search }}" placeholder="Utilisateur, action, description…" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-semibold mb-1.5" for="action">Type d'action</label>
          <select id="action" name="action" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
            <option value="">Toutes les actions</option>
            @foreach ($actionLabels as $value => $label)
              <option value="{{ $value }}" @selected($action === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-lg font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Filtrer</button>
        <a href="{{ route('admin.activity-logs') }}" class="px-5 py-2.5 rounded-lg font-bold border-2 border-gray-300 hover:border-ink transition">Réinitialiser</a>
      </form>

      <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
        <table class="w-full bg-white">
          <thead>
            <tr class="bg-gray-50 text-gray-700 text-xs uppercase">
              <th class="text-left px-4 py-3.5 font-bold">Date</th>
              <th class="text-left px-4 py-3.5 font-bold">Utilisateur</th>
              <th class="text-left px-4 py-3.5 font-bold">Action</th>
              <th class="text-left px-4 py-3.5 font-bold">Description</th>
              <th class="text-left px-4 py-3.5 font-bold">IP</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($logs as $log)
            <tr class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-4 py-3.5 whitespace-nowrap text-sm">{{ $log->created_at?->translatedFormat('d M Y H:i') }}</td>
              <td class="px-4 py-3.5">
                @if ($log->user)
                  <div class="font-semibold">{{ $log->user->firstname }} {{ $log->user->lastname }}</div>
                  <div class="text-gray-500 text-sm">{{ $log->user->email }}</div>
                @else
                  <span class="text-gray-400 text-sm">Système</span>
                @endif
              </td>
              <td class="px-4 py-3.5">
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                  {{ $actionLabels[$log->action] ?? $log->action }}
                </span>
              </td>
              <td class="px-4 py-3.5 text-sm text-gray-700">{{ $log->description ?? '—' }}</td>
              <td class="px-4 py-3.5 whitespace-nowrap text-sm text-gray-500">{{ $log->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucune activité enregistrée.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if ($logs->total() > 0)
        <p class="text-sm text-gray-500 mb-3">
          Affichage de {{ $logs->firstItem() }} à {{ $logs->lastItem() }} sur {{ $logs->total() }} entrée{{ $logs->total() > 1 ? 's' : '' }}
          @if ($logs->hasPages())
            · page {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
          @endif
        </p>
      @endif

      @if ($logs->hasPages())
        <div class="mt-2">
          {{ $logs->links() }}
        </div>
      @endif
    </main>
  </div>
@endsection

@section('footer')
@endsection
