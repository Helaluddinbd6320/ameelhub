<?php

namespace App\Models;

use App\Models\JobDeal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobSelection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'job_post_id',
        'job_interest_id',
        'worker_id',
        'agent_id',
        'agent_fee_sar',
        'notification_sent_at',
        'worker_response',
        'worker_responded_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'agent_fee_sar'        => 'decimal:2',
            'notification_sent_at' => 'datetime',
            'worker_responded_at'  => 'datetime',
            'expires_at'           => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function interest(): BelongsTo
    {
        return $this->belongsTo(JobInterest::class, 'job_interest_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function deal(): HasOne
    {
        return $this->hasOne(JobDeal::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() && $this->worker_response === 'pending';
    }
}