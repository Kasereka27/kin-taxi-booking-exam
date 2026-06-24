<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'in:general,ride,billing,driver,partnership'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Votre nom est obligatoire.',
            'email.required' => 'Votre adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'subject.in' => 'Veuillez choisir un sujet valide.',
            'message.required' => 'Le message est obligatoire.',
            'message.min' => 'Le message doit contenir au moins 10 caractères.',
        ];
    }

    public function subjectLabel(): string
    {
        return match ($this->validated('subject')) {
            'ride' => 'Problème avec une course',
            'billing' => 'Facturation',
            'driver' => 'Devenir chauffeur',
            'partnership' => 'Partenariat',
            default => 'Question générale',
        };
    }
}
