<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdateTwoFactorRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('driverProfile');

        return view('pageContent.profile', [
            'user' => $user,
            'driverProfile' => $user->driverProfile,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Votre profil a été mis à jour.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('success', 'Votre mot de passe a été modifié.');
    }

    public function updateTwoFactor(UpdateTwoFactorRequest $request): RedirectResponse
    {
        $enabled = $request->boolean('two_factor_enabled');

        $request->user()->update([
            'two_factor_enabled' => $enabled,
        ]);

        return redirect()
            ->route('profile.edit')
            ->with(
                'success',
                $enabled
                    ? 'La double authentification est activée. Un code vous sera envoyé par e-mail à chaque connexion.'
                    : 'La double authentification est désactivée.',
            );
    }
}
