<?php

namespace App\Models;

use App\Models\JobDealMilestone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobDeal extends Model
{
    protected $fillable = [
        'uuid',
        'job_selection_id',
        'job_post_id',
        'worker_id',
        'agent_id',
        'admin_id',
        'agent_fee_sar',
        'confirmed_at',
        'working_at',
        'cancelled_at',
        'dispute_raised_by',
        'dispute_reason',
        'resolution_notes',
        'admin_notes',
    ];

    /**
     * Admin-only fields — cannot be mass-assigned
     */
    protected $guarded = [
        'chapai_commission_pct',
        'chapai_commission_sar',
        'agent_receives_sar',
        'status',
        'completed_at',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'agent_fee_sar'         => 'decimal:2',
            'chapai_commission_pct' => 'decimal:2',
            'chapai_commission_sar' => 'decimal:2',
            'agent_receives_sar'    => 'decimal:2',
            'confirmed_at'          => 'datetime',
            'working_at'            => 'datetime',
            'completed_at'          => 'datetime',
            'cancelled_at'          => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function selection(): BelongsTo
    {
        return $this->belongsTo(JobSelection::class, 'job_selection_id');
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(JobDealMilestone::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}