@php
    $activePage = $activePage ?? '';
@endphp

<aside class="w-full h-full lg:min-h-full bg-ink text-gray-300 p-4 sm:p-5 flex flex-row lg:flex-col gap-1 overflow-x-auto lg:overflow-x-hidden lg:overflow-y-auto min-w-0">
  @include('partials.brand-logo', ['class' => 'hidden lg:flex items-center gap-2.5 font-black text-2xl text-white mb-8 px-2'])
  <nav class="flex lg:flex-col gap-1 flex-1">
    <x-dashboard-nav-link :href="route('admin.dashboard')" icon="chart-bar" label="Vue d'ensemble" :active="$activePage === 'dashboard'" />
    <x-dashboard-nav-link :href="route('admin.live-rides')" icon="taxi" label="Courses en cours" :active="$activePage === 'live-rides'" />
    <x-dashboard-nav-link :href="route('admin.users')" icon="users" label="Utilisateurs" :active="$activePage === 'users'" />
    <x-dashboard-nav-link :href="route('admin.activity-logs')" icon="clipboard-document-list" label="Journal d'activité" :active="$activePage === 'activity-logs'" />
  </nav>
  <div class="hidden lg:block border-t border-gray-800 pt-4 mt-4">
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold text-gray-400 hover:bg-gray-800 hover:text-white transition"><x-icon name="arrow-right-on-rectangle" class="w-5 h-5 shrink-0" /> Déconnexion</button>
    </form>
  </div>
</aside>
