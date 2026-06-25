<x-mail::message>
# Code de vérification

Bonjour {{ $user->firstname }},

Utilisez le code ci-dessous pour terminer votre connexion à {{ config('app.name') }} :

<x-mail::panel>
**{{ $code }}**
</x-mail::panel>

Ce code expire dans **{{ $expiresMinutes }} minutes**. Ne le partagez avec personne.

Si vous n'avez pas tenté de vous connecter, ignorez cet e-mail ou contactez le support.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
