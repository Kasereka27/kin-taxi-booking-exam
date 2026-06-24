<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $user = $this->user();

            if ($user && $user->password === null) {
                $validator->errors()->add(
                    'current_password',
                    'Utilisez « Mot de passe oublié » pour définir un mot de passe sur un compte Google.'
                );
            }
        });
    }
}
