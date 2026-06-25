<?php

namespace App\Models;

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
        'eco' => [5000, 3000],
        'confort' => [8000, 4500],
        'van' => [12000, 6000],
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
     * @return array<string, string>
     */
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
