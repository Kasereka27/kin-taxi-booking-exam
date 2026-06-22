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
