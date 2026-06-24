<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(string $token, Request $request): View
    {
        return view('mainPages.reset-password', [
            'token' => $token,
            'email' => $request->string('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.')
            : back()->withErrors(['email' => 'Ce lien de réinitialisation est invalide ou a expiré.']);
    }
}
