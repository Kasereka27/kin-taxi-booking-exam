<?php

namespace App\Http\Resources;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Ride */
class RideResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference(),
            'status' => $this->status,
            'status_label' => $this->statusLabel(),
            'pickup_addr' => $this->pickup_addr,
            'dropoff_addr' => $this->dropoff_addr,
            'vehicle_type' => $this->vehicle_type,
            'price' => $this->price !== null ? (float) $this->price : null,
            'distance_km' => $this->distance_km !== null ? (float) $this->distance_km : null,
            'is_paid' => $this->isPaid(),
            'requested_at' => $this->requested_at?->toIso8601String(),
            'accepted_at' => $this->accepted_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'firstname' => $this->client->firstname,
                'lastname' => $this->client->lastname,
            ]),
            'driver' => $this->whenLoaded('driver', fn () => $this->driver ? [
                'id' => $this->driver->id,
                'firstname' => $this->driver->firstname,
                'lastname' => $this->driver->lastname,
            ] : null),
        ];
    }
}
