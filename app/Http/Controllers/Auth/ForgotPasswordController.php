<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('mainPages.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::ResetLinkSent
            ? back()->with('status', 'Si un compte existe avec cette adresse, un lien de réinitialisation vous a été envoyé.')
            : back()->withErrors(['email' => 'Impossible d\'envoyer le lien pour cette adresse.']);
    }
}
