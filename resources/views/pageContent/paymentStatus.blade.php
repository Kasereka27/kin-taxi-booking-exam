@extends('mainPages.app', ['cssClass' => 'font-sans text-ink bg-gray-50'])

@section('title', 'Statut du paiement')

@php
    $statusMeta = match ($payment->status) {
        'success' => ['label' => 'Paiement réussi', 'badge' => 'bg-green-100 text-green-700', 'icon' => '✅'],
        'failed' => ['label' => 'Paiement refusé', 'badge' => 'bg-red-100 text-red-700', 'icon' => '❌'],
        default => ['label' => 'En attente de validation', 'badge' => 'bg-yellow-100 text-yellow-700', 'icon' => '⏳'],
    };

    $failureMessage = match ($payment->failure_reason) {
        'expired' => "Le code PIN de confirmation n'a pas été saisi à temps sur votre téléphone. Le paiement a été refusé.",
        'declined' => "Le paiement a été refusé par votre opérateur Mobile Money.",
        default => "Le paiement n'a pas abouti.",
    };
@endphp

@section('content')
<section class="py-10">
  <div class="max-w-md mx-auto px-5">
    <div class="bg-white rounded-2xl p-7 shadow-soft border border-gray-200 text-center">
      <div class="text-5xl mb-3">{{ $statusMeta['icon'] }}</div>
      <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold {{ $statusMeta['badge'] }}">{{ $statusMeta['label'] }}</span>

      <h1 class="text-2xl font-extrabold mt-4">@fc($payment->amount)</h1>
      @if ($payment->fee > 0)
        <p class="text-gray-400 text-xs mt-1">dont @fc($payment->fee) de frais Labyrinthe</p>
      @endif
      <p class="text-gray-500">Course #{{ \App\Models\Ride::referenceFor($payment->ride_id) }} · {{ strtoupper($payment->method) }}</p>

      @if (session('status'))
        <div class="mt-5 px-4 py-3 rounded-xl bg-blue-50 text-blue-800 text-sm font-semibold">{{ session('status') }}</div>
      @endif

      @if ($payment->isPending())
        <p class="text-gray-500 text-sm mt-5">Validez la demande sur votre téléphone avec votre code PIN.</p>
        <div class="flex items-center justify-center gap-2 text-gray-500 text-sm mt-4">
          <span class="inline-block w-4 h-4 border-2 border-gray-300 border-t-taxi rounded-full animate-spin"></span>
          Vérification automatique du paiement…
        </div>
        <a href="{{ route('payments.status', $payment) }}" class="mt-5 inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink transition">Rafraîchir maintenant</a>
      @elseif ($payment->isSuccessful())
        <p class="text-gray-500 text-sm mt-5">Merci ! Votre course est réglée. Votre reçu PDF est disponible ci-dessous.</p>
        <div class="mt-5 flex flex-col sm:flex-row gap-3 justify-center">
          <a href="{{ route('payments.receipt', $payment) }}" class="inline-flex px-6 py-3 rounded-full font-bold border-2 border-gray-300 hover:border-ink hover:bg-ink hover:text-white transition">Télécharger le reçu PDF</a>
          <a href="{{ route('rides.show', $payment->ride_id) }}" class="inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Voir la course</a>
        </div>
      @else
        <p class="text-gray-600 text-sm mt-5">{{ $failureMessage }} Vous pouvez réessayer.</p>
        <a href="{{ route('rides.pay', $payment->ride_id) }}" class="mt-5 inline-flex px-6 py-3 rounded-full font-bold bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réessayer le paiement</a>
      @endif

      <div class="mt-4">
        <a href="{{ route('user.dashboardClient') }}" class="text-taxi-dark font-semibold text-sm">Retour au tableau de bord</a>
      </div>
    </div>
  </div>
</section>

@if ($payment->isPending())
<script>
  (function () {
    const url = "{{ route('payments.poll', $payment) }}";
    const deadline = Date.now() + {{ ((int) config('labyrinthe.payment_timeout', 120) + 20) * 1000 }};
    const interval = setInterval(async () => {
      // Filet de sécurité : au-delà du délai, on recharge pour afficher le refus.
      if (Date.now() > deadline) {
        clearInterval(interval);
        window.location.reload();
        return;
      }
      try {
        const res = await fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.status && data.status !== 'pending') {
          clearInterval(interval);
          window.location.reload();
        }
      } catch (e) {
        // silencieux : on réessaiera au prochain tick
      }
    }, 5000);
  })();
</script>
@endif
@endsection
