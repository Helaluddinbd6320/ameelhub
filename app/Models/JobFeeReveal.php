<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobFeeReveal extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'job_post_id',
        'amount_charged',
        'ip_address',
        'revealed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_charged' => 'decimal:2',
            'revealed_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobPost(): BelongsTo
    {
        return $this->belongsTo(JobPost::class);
    }
}