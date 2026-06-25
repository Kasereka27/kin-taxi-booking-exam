<x-mail::message>
# Confirmez votre adresse e-mail

Bonjour {{ $user->firstname }},

Pour sécuriser votre compte **{{ config('app.name') }}**, veuillez confirmer votre adresse e-mail en cliquant sur le bouton ci-dessous.

<x-mail::button :url="$verificationUrl">
Confirmer mon e-mail
</x-mail::button>

Si vous n'avez pas créé de compte, ignorez ce message.

Merci,<br>
{{ config('app.name') }}
</x-mail::message>
