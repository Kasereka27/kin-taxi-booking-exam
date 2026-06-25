<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'two_factor_enabled' => $this->boolean('two_factor_enabled'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->user();

        return [
            'two_factor_enabled' => ['required', 'boolean'],
            'current_password' => [
                Rule::requiredIf(fn () => $user && $user->password !== null),
                'string',
                'current_password',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'two_factor_enabled.required' => 'Indiquez si vous souhaitez activer ou désactiver la double authentification.',
            'current_password.required' => 'Confirmez votre mot de passe pour modifier la double authentification.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
        ];
    }
}
