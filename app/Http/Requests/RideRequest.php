<?php

namespace App\Http\Requests;

use App\Models\Ride;
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
        $bounds = Ride::kinshasaBounds();

        return [
            'pickup_addr' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['required', 'numeric', "between:{$bounds['lat_min']},{$bounds['lat_max']}"],
            'pickup_lng' => ['required', 'numeric', "between:{$bounds['lng_min']},{$bounds['lng_max']}"],
            'dropoff_addr' => ['required', 'string', 'max:255'],
            'dropoff_lat' => ['required', 'numeric', "between:{$bounds['lat_min']},{$bounds['lat_max']}"],
            'dropoff_lng' => ['required', 'numeric', "between:{$bounds['lng_min']},{$bounds['lng_max']}"],
            'route_polyline' => ['nullable', 'string'],
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
            'pickup_lat.required' => 'Sélectionnez une adresse de départ dans la liste de suggestions (Kinshasa).',
            'pickup_lng.required' => 'Sélectionnez une adresse de départ dans la liste de suggestions (Kinshasa).',
            'dropoff_lat.required' => 'Sélectionnez une adresse de destination dans la liste de suggestions (Kinshasa).',
            'dropoff_lng.required' => 'Sélectionnez une adresse de destination dans la liste de suggestions (Kinshasa).',
            'pickup_lat.between' => 'L\'adresse de départ doit se situer dans la zone de Kinshasa.',
            'pickup_lng.between' => 'L\'adresse de départ doit se situer dans la zone de Kinshasa.',
            'dropoff_lat.between' => 'L\'adresse de destination doit se situer dans la zone de Kinshasa.',
            'dropoff_lng.between' => 'L\'adresse de destination doit se situer dans la zone de Kinshasa.',
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

    /**
     * @return list<array{0: float, 1: float}>|null
     */
    public function decodedRoutePolyline(): ?array
    {
        $raw = $this->input('route_polyline');

        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        $path = [];

        foreach ($decoded as $point) {
            if (! is_array($point) || count($point) < 2) {
                return null;
            }

            $lat = (float) $point[0];
            $lng = (float) $point[1];

            if (! Ride::isWithinKinshasa($lat, $lng)) {
                return null;
            }

            $path[] = [$lat, $lng];
        }

        return count($path) >= 2 ? $path : null;
    }
}
