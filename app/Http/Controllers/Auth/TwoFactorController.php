<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyTwoFactorRequest;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    public function show(): View|RedirectResponse
    {
        $user = $this->twoFactorService->pendingUser();

        if ($user === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Votre session de vérification a expiré. Reconnectez-vous.']);
        }

        return view('mainPages.twoFactor', [
            'email' => $user->email,
        ]);
    }

    public function store(VerifyTwoFactorRequest $request): RedirectResponse
    {
        $user = $this->twoFactorService->pendingUser();

        if ($user === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Votre session de vérification a expiré. Reconnectez-vous.']);
        }

        if (! $this->twoFactorService->verify($user, $request->validated('code'))) {
            return back()
                ->withErrors(['code' => 'Code invalide ou expiré.'])
                ->onlyInput('code');
        }

        $authenticatedUser = $this->twoFactorService->completePendingLogin();

        if ($authenticatedUser === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Impossible de finaliser la connexion.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route($authenticatedUser->dashboardRouteName()));
    }

    public function resend(): RedirectResponse
    {
        $user = $this->twoFactorService->pendingUser();

        if ($user === null) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Votre session de vérification a expiré. Reconnectez-vous.']);
        }

        $this->twoFactorService->send($user);

        return redirect()
            ->route('two-factor.show')
            ->with('status', 'Un nouveau code a été envoyé à votre adresse e-mail.');
    }

    public function cancel(): RedirectResponse
    {
        $this->twoFactorService->clearPending();

        return redirect()
            ->route('login')
            ->with('status', 'Connexion annulée.');
    }
}
