<x-mail::message>
# Réservation confirmée

Bonjour {{ $ride->client?->firstname ?? 'client' }},

Votre course **{{ $ride->reference() }}** a bien été enregistrée.

**Départ :** {{ $ride->pickup_addr }}  
**Destination :** {{ $ride->dropoff_addr }}  
**Véhicule :** {{ ucfirst($ride->vehicle_type) }}  
**Estimation :** {{ $priceLabel }}

Nous recherchons un chauffeur disponible. Vous serez notifié dès qu'une course sera acceptée.

<x-mail::button :url="route('rides.show', $ride)">
Voir ma course
</x-mail::button>

À bientôt,<br>
{{ config('app.name') }}
</x-mail::message>
