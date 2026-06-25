<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogService;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private TwoFactorService $twoFactorService,
        private ActivityLogService $activityLogService,
    ) {}

    public function login(): View
    {
        return view('mainPages.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Identifiants incorrects.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Votre compte est désactivé. Contactez l\'administrateur.'])
                ->onlyInput('email');
        }

        if ($user->two_factor_enabled) {
            return $this->twoFactorService->initiatePendingLogin($user, $request->boolean('remember'));
        }

        $request->session()->regenerate();

        $this->activityLogService->log(
            ActivityLogService::ACTION_LOGIN,
            'Connexion réussie via le formulaire web.',
            $user,
            $request,
        );

        return redirect()->intended(route($user->dashboardRouteName()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $this->activityLogService->log(
                ActivityLogService::ACTION_LOGOUT,
                'Déconnexion depuis l\'application web.',
                $user,
                $request,
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
