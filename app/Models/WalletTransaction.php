<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'direction',
        'balance_type',
        'available_before',
        'available_after',
        'held_before',
        'held_after',
        'reference_type',
        'reference_id',
        'description',
        'created_by_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'available_before' => 'decimal:2',
            'available_after'  => 'decimal:2',
            'held_before'      => 'decimal:2',
            'held_after'       => 'decimal:2',
            'created_at'       => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isCredit(): bool
    {
        return $this->direction === 'credit';
    }

    public function isDebit(): bool
    {
        return $this->direction === 'debit';
    }
}