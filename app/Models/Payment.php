<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'ride_id',
        'user_id',
        'order_number',
        'method',
        'provider_reference',
        'amount',
        'fee',
        'currency',
        'status',
        'failure_reason',
        'receipt_path',
        'callback_payload',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Montant net encaissé par TaxiGo (montant payé moins la commission Labyrinthe).
     */
    public function netAmount(): float
    {
        return (float) $this->amount - (float) $this->fee;
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
