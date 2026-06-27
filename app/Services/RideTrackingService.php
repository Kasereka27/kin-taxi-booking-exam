<?php

namespace App\Services;

use App\Events\RideTrackingUpdated;
use App\Models\Ride;

class RideTrackingService
{
    public function initializeTracking(Ride $ride): void
    {
        $ride->loadMissing(['driver.driverProfile']);
        $profile = $ride->driver?->driverProfile;

        if ($profile === null) {
            return;
        }

        [$lat, $lng] = $ride->pickupCoordinates();
        $profile->update([
            'current_lat' => $lat - 0.018,
            'current_lng' => $lng - 0.012,
        ]);

        $ride->refresh();
        broadcast(new RideTrackingUpdated($ride));
    }

    public function updateDriverPosition(Ride $ride, float $lat, float $lng): Ride
    {
        $ride->loadMissing(['driver.driverProfile']);
        $profile = $ride->driver?->driverProfile;

        if ($profile !== null) {
            $profile->update([
                'current_lat' => $lat,
                'current_lng' => $lng,
            ]);
        }

        $this->syncStatusFromPosition($ride, $lat, $lng);
        $ride->refresh();

        broadcast(new RideTrackingUpdated($ride));

        return $ride;
    }

    /**
     * @return array{ride_id: int, status: string, lat: float|null, lng: float|null, eta_minutes: int}
     */
    public function trackingPayload(Ride $ride): array
    {
        $ride->loadMissing(['driver.driverProfile']);
        $coords = $ride->driverCoordinates();

        return [
            'ride_id' => $ride->id,
            'status' => $ride->status,
            'lat' => $coords[0] ?? null,
            'lng' => $coords[1] ?? null,
            'eta_minutes' => $this->estimateEtaMinutes($ride),
        ];
    }

    public function estimateEtaMinutes(Ride $ride): int
    {
        $coords = $ride->driverCoordinates();

        if ($coords === null) {
            return $ride->distance_km
                ? max(3, (int) round((float) $ride->distance_km * 1.8 + 3))
                : 8;
        }

        $target = in_array($ride->status, ['assigned', 'approche'], true)
            ? $ride->pickupCoordinates()
            : $ride->dropoffCoordinates();

        $distanceKm = $this->distanceKm($coords[0], $coords[1], $target[0], $target[1]);

        return max(1, (int) round($distanceKm * 1.8 + 2));
    }

    private function syncStatusFromPosition(Ride $ride, float $lat, float $lng): void
    {
        if (! in_array($ride->status, ['assigned', 'approche', 'course'], true)) {
            return;
        }

        [$pickupLat, $pickupLng] = $ride->pickupCoordinates();
        $distToPickupKm = $this->distanceKm($lat, $lng, $pickupLat, $pickupLng);

        $newStatus = match ($ride->status) {
            'assigned' => 'approche',
            'approche' => $distToPickupKm <= 0.15 ? 'course' : 'approche',
            default => $ride->status,
        };

        if ($newStatus !== $ride->status) {
            $ride->update(['status' => $newStatus]);
        }
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
