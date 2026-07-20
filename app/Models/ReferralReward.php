<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralReward extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'referrer_id',
        'referee_id',
        'reward_amount',
        'status',
        'paid_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'reward_amount' => 'decimal:2',
            'paid_at'       => 'datetime',
            'created_at'    => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }
}