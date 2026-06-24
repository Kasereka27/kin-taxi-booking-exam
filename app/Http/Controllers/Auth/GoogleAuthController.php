<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'La connexion Google n\'est pas configurée sur ce serveur.']);
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'La connexion Google n\'est pas configurée sur ce serveur.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Connexion Google annulée ou impossible.']);
        }

        $user = $this->resolveUser($googleUser);

        if (! $user->is_active) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Votre compte est désactivé. Contactez l\'administrateur.']);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route($user->dashboardRouteName()));
    }

    private function resolveUser(SocialiteUser $googleUser): User
    {
        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if ($user) {
            return $user;
        }

        $user = User::query()->where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            return $user;
        }

        [$firstname, $lastname] = $this->splitName($googleUser->getName());

        return User::query()->create([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'password' => Str::password(32),
            'role' => 'client',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(?string $name): array
    {
        $name = trim((string) $name);

        if ($name === '') {
            return ['Utilisateur', 'Google'];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [
            $parts[0],
            $parts[1] ?? '',
        ];
    }
}
