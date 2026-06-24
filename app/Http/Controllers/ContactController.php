<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Envoie le message de contact à l'équipe support.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Mail::to(config('mail.contact_address'))
            ->send(new ContactMessage(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                subjectLabel: $request->subjectLabel(),
                body: $validated['message'],
            ));

        return back()->with('success', 'Votre message a bien été envoyé. Nous vous répondrons sous 24 h.');
    }
}
