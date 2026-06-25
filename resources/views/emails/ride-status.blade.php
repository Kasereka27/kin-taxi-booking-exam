<x-mail::message>
# {{ $headline }}

{{ $details }}

**Course :** {{ $ride->reference() }}  
**Trajet :** {{ $ride->pickup_addr }} → {{ $ride->dropoff_addr }}  
**Statut :** {{ $ride->statusLabel() }}

<x-mail::button :url="route('rides.show', $ride)">
Voir la course
</x-mail::button>

{{ config('app.name') }}
</x-mail::message>
