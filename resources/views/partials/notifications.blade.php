@php
    $notifUser = auth()->user();
    $unreadCount = $notifUser->unreadNotifications()->count();
    $recentNotifs = $notifUser->notifications()->latest()->limit(6)->get();
@endphp

<div class="relative">
  <button id="notifToggle" type="button" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 transition" aria-label="Notifications">
    <span class="text-xl">🔔</span>
    @if ($unreadCount > 0)
      <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold grid place-items-center">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
  </button>

  <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-2xl shadow-soft border border-gray-200 overflow-hidden z-50">
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
      <span class="font-bold">Notifications</span>
      @if ($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
          @csrf
          <button type="submit" class="text-xs font-semibold text-taxi-dark hover:underline">Tout marquer lu</button>
        </form>
      @endif
    </div>

    <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
      @forelse ($recentNotifs as $notif)
        <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
          @csrf
          <button type="submit" class="w-full text-left flex gap-3 px-4 py-3 hover:bg-gray-50 transition {{ $notif->read_at ? '' : 'bg-yellow-50' }}">
            <span class="text-lg shrink-0">{{ $notif->data['icon'] ?? '🔔' }}</span>
            <span class="min-w-0">
              <span class="block font-semibold text-sm truncate">{{ $notif->data['title'] ?? 'Notification' }}</span>
              <span class="block text-xs text-gray-500 line-clamp-2">{{ $notif->data['message'] ?? '' }}</span>
              <span class="block text-[11px] text-gray-400 mt-0.5">{{ $notif->created_at->diffForHumans() }}</span>
            </span>
          </button>
        </form>
      @empty
        <p class="px-4 py-6 text-center text-sm text-gray-500">Aucune notification pour le moment.</p>
      @endforelse
    </div>

    <a href="{{ route('notifications.index') }}" class="block px-4 py-3 text-center text-sm font-semibold text-taxi-dark border-t border-gray-100 hover:bg-gray-50">Voir toutes les notifications</a>
  </div>
</div>
