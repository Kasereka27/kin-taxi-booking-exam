<x-mail::message>
# Bienvenue, {{ $user->firstname }} !

Votre compte **{{ config('app.name') }}** est prêt. Vous pouvez dès maintenant :

- Réserver une course en quelques clics
- Suivre votre chauffeur en temps réel
- Consulter votre historique et vos paiements

<x-mail::button :url="route($user->dashboardRouteName())">
Accéder à mon espace
</x-mail::button>

Merci de nous faire confiance,<br>
{{ config('app.name') }}
</x-mail::message>
