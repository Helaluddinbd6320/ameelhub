<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechargeRequest extends Model
{
    /**
     * WithdrawalRequest মডেলের মতো একই কারণে $guarded-only প্যাটার্ন:
     * user_id/status/admin_note/processed_by_id/processed_at কখনো
     * ইউজারের ফর্ম থেকে mass-assign হওয়া উচিত না — নাহলে একজন worker
     * নিজের request নিজেই 'approved' বানিয়ে ফেলতে পারত, অথবা user_id
     * বদলে অন্যের নামে recharge বসাতে পারত।
     *
     * amount/payment_method/reference_number/proof_file — এগুলো
     * ইউজার-ইনপুট, তাই non-guarded (mass-assignable)।
     */
    protected $guarded = [
        'user_id',
        'status',
        'admin_note',
        'processed_by_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}