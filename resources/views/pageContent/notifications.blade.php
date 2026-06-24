@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Mes notifications')

@section('content')
<section class="py-10">
  <div class="max-w-2xl mx-auto px-5">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-extrabold">Notifications</h1>
      @if (auth()->user()->unreadNotifications()->count() > 0)
        <form method="POST" action="{{ route('notifications.readAll') }}">
          @csrf
          <button type="submit" class="text-sm font-semibold text-taxi-dark hover:underline">Tout marquer comme lu</button>
        </form>
      @endif
    </div>

    @if (session('success'))
      <div class="mb-5 px-4 py-3 rounded-xl bg-green-100 text-green-800 text-sm font-semibold">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-soft border border-gray-200 overflow-hidden divide-y divide-gray-100">
      @forelse ($notifications as $notif)
        <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
          @csrf
          <button type="submit" class="w-full text-left flex gap-4 px-5 py-4 hover:bg-gray-50 transition {{ $notif->read_at ? '' : 'bg-yellow-50' }}">
            <span class="text-2xl shrink-0">{{ $notif->data['icon'] ?? '🔔' }}</span>
            <span class="min-w-0 flex-1">
              <span class="flex items-center gap-2">
                <span class="font-semibold">{{ $notif->data['title'] ?? 'Notification' }}</span>
                @unless ($notif->read_at)
                  <span class="w-2 h-2 rounded-full bg-red-500"></span>
                @endunless
              </span>
              <span class="block text-sm text-gray-600 mt-0.5">{{ $notif->data['message'] ?? '' }}</span>
              <span class="block text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</span>
            </span>
          </button>
        </form>
      @empty
        <p class="px-5 py-10 text-center text-gray-500">Vous n'avez aucune notification.</p>
      @endforelse
    </div>

    <div class="mt-6">
      {{ $notifications->links() }}
    </div>
  </div>
</section>
@endsection
