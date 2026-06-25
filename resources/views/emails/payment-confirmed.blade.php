<x-mail::message>
# Paiement confirmé

Bonjour {{ $payment->user?->firstname ?? 'client' }},

Votre paiement de **{{ $amountLabel }}** pour la course **{{ $rideReference }}** a bien été enregistré. Votre reçu PDF est joint à cet e-mail.

<x-mail::button :url="route('rides.show', $payment->ride_id)">
Voir la course
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}
</x-mail::message>
