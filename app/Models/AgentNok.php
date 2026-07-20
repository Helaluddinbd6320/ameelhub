<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AgentNok extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'job_post_id',
        'agent_id',
        'worker_id',
        'worker_user_id',
        'nok_message',
        'route_source',
        'status',
        'sent_at',
        'responded_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at'      => 'datetime',
            'responded_at' => 'datetime',
            'expires_at'   => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function workerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_user_id');
    }

    public function interest(): HasOne
    {
        return $this->hasOne(JobInterest::class, 'nok_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() && $this->status === 'pending';
    }
}