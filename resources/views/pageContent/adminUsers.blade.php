@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Gestion des utilisateurs')

@section('header')
@show

@php
    $roleLabels = ['admin' => 'Administrateur', 'driver' => 'Chauffeur', 'client' => 'Client'];
    $roleStyles = ['admin' => 'bg-purple-100 text-purple-700', 'driver' => 'bg-blue-100 text-blue-700', 'client' => 'bg-gray-100 text-gray-700'];
@endphp

@section('content')
<div class="grid lg:grid-cols-[260px_1fr] min-h-screen">
    @include('partials.admin-sidebar', ['activePage' => 'users'])

    <main class="p-4 sm:p-7 lg:px-9">
      <div class="flex justify-between items-center mb-7 flex-wrap gap-3">
        <div><h1 class="text-2xl font-extrabold">Utilisateurs</h1><p class="text-gray-500">Gérez les comptes clients, chauffeurs et administrateurs.</p></div>
      </div>

      @if (session('status'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-green-100 text-green-800 text-sm font-semibold">{{ session('status') }}</div>
      @endif
      @if (session('error'))
        <div class="mb-5 px-4 py-3 rounded-xl bg-red-100 text-red-800 text-sm font-semibold">{{ session('error') }}</div>
      @endif

      <form method="GET" action="{{ route('admin.users') }}" class="bg-white rounded-2xl p-5 shadow-xs border border-gray-200 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-semibold mb-1.5" for="search">Recherche</label>
          <input id="search" name="search" value="{{ $search }}" placeholder="Nom, email ou téléphone…" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-semibold mb-1.5" for="role">Rôle</label>
          <select id="role" name="role" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
            <option value="">Tous les rôles</option>
            <option value="client" @selected($role === 'client')>Client</option>
            <option value="driver" @selected($role === 'driver')>Chauffeur</option>
            <option value="admin" @selected($role === 'admin')>Administrateur</option>
          </select>
        </div>
        <div class="min-w-[180px]">
          <label class="block text-sm font-semibold mb-1.5" for="status">Statut du compte</label>
          <select id="status" name="status" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:border-ink outline-none">
            <option value="">Tous les statuts</option>
            <option value="active" @selected($status === 'active')>Actifs</option>
            <option value="blocked" @selected($status === 'blocked')>Bloqués</option>
          </select>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-lg font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Filtrer</button>
        <a href="{{ route('admin.users') }}" class="px-5 py-2.5 rounded-lg font-bold border-2 border-gray-300 hover:border-ink transition">Réinitialiser</a>
      </form>

      <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6">
        <table class="w-full bg-white">
          <thead><tr class="bg-gray-50 text-gray-700 text-xs uppercase"><th class="text-left px-4 py-3.5 font-bold">Utilisateur</th><th class="text-left px-4 py-3.5 font-bold">Téléphone</th><th class="text-left px-4 py-3.5 font-bold">Rôle</th><th class="text-left px-4 py-3.5 font-bold">Statut</th><th class="text-left px-4 py-3.5 font-bold">Inscrit le</th><th class="text-left px-4 py-3.5 font-bold">Action</th></tr></thead>
          <tbody>
            @forelse ($users as $user)
            <tr class="border-t border-gray-200 hover:bg-gray-50">
              <td class="px-4 py-3.5">
                <div class="font-semibold">{{ $user->firstname }} {{ $user->lastname }}</div>
                <div class="text-gray-500 text-sm">{{ $user->email }}</div>
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ $user->phone ?? '—' }}</td>
              <td class="px-4 py-3.5"><span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $roleStyles[$user->role] ?? 'bg-gray-100 text-gray-700' }}">{{ $roleLabels[$user->role] ?? $user->role }}</span></td>
              <td class="px-4 py-3.5">
                @if ($user->is_active)
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">Actif</span>
                @else
                  <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">Bloqué</span>
                @endif
              </td>
              <td class="px-4 py-3.5 whitespace-nowrap">{{ $user->created_at?->translatedFormat('d M Y') }}</td>
              <td class="px-4 py-3.5">
                @if ($user->isAdmin())
                  <span class="text-gray-400 text-sm">—</span>
                @else
                  <div class="flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                      @csrf
                      @method('PATCH')
                      @if ($user->is_active)
                        <button type="submit" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold border-2 border-red-300 text-red-600 hover:bg-red-600 hover:text-white hover:border-red-600 transition">Bloquer</button>
                      @else
                        <button type="submit" class="inline-flex px-3 py-1.5 rounded-full text-xs font-bold border-2 border-green-300 text-green-700 hover:bg-green-600 hover:text-white hover:border-green-600 transition">Réactiver</button>
                      @endif
                    </form>
                    @unless ($user->is(auth()->user()))
                      <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer définitivement ce compte ? Cette action est irréversible pour l\'utilisateur.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-red-600 text-white hover:bg-red-700 transition">
                          <x-icon name="x-circle" class="w-3.5 h-3.5" />
                          Supprimer
                        </button>
                      </form>
                    @endunless
                  </div>
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun utilisateur trouvé.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{ $users->links() }}
    </main>
  </div>
@endsection

@section('footer')
@endsection
