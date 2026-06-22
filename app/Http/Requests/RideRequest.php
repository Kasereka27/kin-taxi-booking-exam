<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RideRequest extends FormRequest
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
            'pickup_addr' => ['required', 'string', 'max:255'],
            'dropoff_addr' => ['required', 'string', 'max:255'],
            'vehicle_type' => ['required', 'in:eco,confort,van'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pickup_addr.required' => 'L\'adresse de départ est obligatoire.',
            'dropoff_addr.required' => 'L\'adresse de destination est obligatoire.',
            'vehicle_type.required' => 'Veuillez choisir un type de véhicule.',
            'vehicle_type.in' => 'Le type de véhicule sélectionné est invalide.',
        ];
    }

    /**
     * Le formulaire envoie « vehicleType » (camelCase) ; on l'aligne sur la colonne.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('vehicleType') && ! $this->has('vehicle_type')) {
            $this->merge(['vehicle_type' => $this->input('vehicleType')]);
        }
    }
}
