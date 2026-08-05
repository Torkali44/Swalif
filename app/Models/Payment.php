<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_id',
        'gateway',
        'gateway_reference',
        'payment_reference',   // PAY-XXXXXXXX — human-readable receipt number
        'amount',
        'currency',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta'   => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // ─── Status Helpers ───────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isWaitingReview(): bool
    {
        return $this->status === 'waiting_review';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeClaimed(): bool
    {
        return $this->status === 'pending';
    }

    public function canBeConfirmed(): bool
    {
        return in_array($this->status, ['pending', 'waiting_review'], true);
    }

    // ─── Factory ──────────────────────────────────────────────────────────────

    /**
     * Generate a unique, human-readable payment reference (PAY-XXXXXXXX).
     * Guaranteed unique via retry loop.
     */
    public static function generateReference(): string
    {
        do {
            $ref = 'PAY-' . strtoupper(Str::random(8));
        } while (self::where('payment_reference', $ref)->exists());

        return $ref;
    }
}
