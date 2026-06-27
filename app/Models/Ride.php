<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ride extends Model
{
    use HasFactory;

    /**
     * Tarifs en francs congolais (FC) par type de véhicule : [prise en charge, prix au km].
     *
     * @var array<string, array{0: int, 1: int}>
     */
    public const RATES = [
        'eco' => [2000, 800],
        'confort' => [3500, 1200],
        'van' => [5000, 1800],
    ];

    /** @var list<string> */
    protected $fillable = [
        'client_id',
        'driver_id',
        'pickup_addr',
        'pickup_lat',
        'pickup_lng',
        'dropoff_addr',
        'dropoff_lat',
        'dropoff_lng',
        'vehicle_type',
        'status',
        'price',
        'distance_km',
        'requested_at',
        'accepted_at',
        'completed_at',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pickup_lat' => 'decimal:7',
            'pickup_lng' => 'decimal:7',
            'dropoff_lat' => 'decimal:7',
            'dropoff_lng' => 'decimal:7',
            'price' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'requested_at' => 'datetime',
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function estimatePrice(string $vehicleType, float $distanceKm): float
    {
        [$base, $perKm] = self::RATES[$vehicleType] ?? self::RATES['eco'];

        return (float) round($base + $distanceKm * $perKm);
    }

    /**
     * @return array{lat_min: float, lat_max: float, lng_min: float, lng_max: float}
     */
    public static function kinshasaBounds(): array
    {
        return [
            'lat_min' => -4.6,
            'lat_max' => -4.2,
            'lng_min' => 15.0,
            'lng_max' => 15.6,
        ];
    }

    public static function isWithinKinshasa(float $lat, float $lng): bool
    {
        $bounds = self::kinshasaBounds();

        return $lat >= $bounds['lat_min']
            && $lat <= $bounds['lat_max']
            && $lng >= $bounds['lng_min']
            && $lng <= $bounds['lng_max'];
    }

    public static function distanceKmBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    /**
     * @return array<string, string>
     */
    /**
     * @return list<string>
     */
    public static function liveStatuses(): array
    {
        return ['pending', 'assigned', 'approche', 'course'];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'En attente',
            'assigned' => 'Assignée',
            'approche' => 'En approche',
            'course' => 'En course',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeLive($query)
    {
        return $query->whereIn('status', self::liveStatuses());
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Indique si la course a déjà été réglée avec succès.
     */
    public function isPaid(): bool
    {
        return $this->payments()->where('status', 'success')->exists();
    }

    public function successfulPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', 'success')
            ->latest('paid_at')
            ->first();
    }

    /**
     * Indique si la course est éligible à un paiement (terminée et non encore réglée).
     */
    public function isPayable(): bool
    {
        return $this->status === 'completed' && ! $this->isPaid();
    }

    /**
     * Référence publique de la course (ex. KTB-42).
     */
    public function reference(): string
    {
        return self::referenceFor($this->id);
    }

    /**
     * Référence publique à partir de l'identifiant numérique.
     */
    public static function referenceFor(int|string $id): string
    {
        return 'KTB-'.$id;
    }

    /**
     * Statuts pour lesquels le suivi en direct est pertinent.
     *
     * @var list<string>
     */
    public static function trackableStatuses(): array
    {
        return ['pending', 'assigned', 'approche', 'course'];
    }

    public function isTrackable(): bool
    {
        return in_array($this->status, self::trackableStatuses(), true);
    }

    /**
     * Coordonnées [lat, lng] du point de prise en charge (Kinshasa par défaut si absentes).
     *
     * @return array{0: float, 1: float}
     */
    public function pickupCoordinates(): array
    {
        if ($this->pickup_lat !== null && $this->pickup_lng !== null) {
            return [(float) $this->pickup_lat, (float) $this->pickup_lng];
        }

        return self::fallbackCoordinates((int) $this->id, 1);
    }

    /**
     * @return array{0: float, 1: float}
     */
    public function dropoffCoordinates(): array
    {
        if ($this->dropoff_lat !== null && $this->dropoff_lng !== null) {
            return [(float) $this->dropoff_lat, (float) $this->dropoff_lng];
        }

        return self::fallbackCoordinates((int) $this->id, 2);
    }

    /**
     * Position estimée du chauffeur pour la carte (profil ou zone proche du départ).
     *
     * @return array{0: float, 1: float}|null
     */
    public function driverCoordinates(): ?array
    {
        if ($this->driver_id === null) {
            return null;
        }

        $profile = $this->driver?->driverProfile;

        if ($profile?->current_lat !== null && $profile?->current_lng !== null) {
            return [(float) $profile->current_lat, (float) $profile->current_lng];
        }

        [$lat, $lng] = $this->pickupCoordinates();

        return [$lat - 0.018, $lng - 0.012];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function fallbackCoordinates(int $id, int $slot): array
    {
        $baseLat = -4.3217;
        $baseLng = 15.3125;
        $spread = (($id * 11 + $slot * 17) % 50) / 1000;

        return [$baseLat + $spread, $baseLng + ($spread * 0.8)];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasMany<Rating, $this>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }
}
