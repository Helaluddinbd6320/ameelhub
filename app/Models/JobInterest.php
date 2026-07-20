<?php

namespace App\Models;

use App\Models\AgentNok;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobInterest extends Model
{
    public $timestamps = false;

    // Worker/agent-provided fields are fillable.
    // System-controlled fields are guarded — must use forceFill().
    protected $fillable = [
        'job_post_id',
        'worker_id',
        'user_id',
        'interest_note',
        'interest_source',
    ];

    protected $guarded = [
        'status',
        'fee_reveal_id',
        'interested_by_id',
        'nok_id',
    ];

    protected function casts(): array
    {
        return [
            'interested_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function interestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interested_by_id');
    }

    public function feeReveal(): BelongsTo
    {
        return $this->belongsTo(JobFeeReveal::class, 'fee_reveal_id');
    }

    public function nok(): BelongsTo
    {
        return $this->belongsTo(AgentNok::class, 'nok_id');
    }
}