<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AgentProfile extends Model
{
    /**
     * SECURITY FIX (Step 10.7 audit):
     * 'user_id' আগে guarded লিস্টে ছিল না। এই মডেলে কোনো $fillable
     * define করা নেই, তাই non-guarded field mass-assignable — অর্থাৎ
     * document-upload ফর্ম থেকে (passport_copy, nid_copy ইত্যাদি) সঠিকভাবে
     * fill হওয়ার কথা থাকলেও, defense-in-depth হিসেবে user_id ও guard
     * করা উচিত, কারণ এই field কখনোই request input থেকে সেট হওয়ার কথা না
     * — শুধু UserObserver প্রোগ্রামেটিক্যালি সেট করবে।
     *
     * ⚠️ NOTE: Agent auto-profile creation (Blueprint Section 13,
     * UserObserver) এ AgentProfile::create(['user_id' => ..., 'is_verified'
     * => false]) কল করা হয় — এখন user_id + is_verified দুটোই guarded
     * হওয়ায় এই কলও অবশ্যই forceCreate() এ বদলাতে হবে।
     */
    protected $guarded = [
        'id',
        'uuid',
        'user_id',
        'is_verified',
        'verified_by_id',
        'verified_at',
        'verification_notes',
        'total_cvs_submitted',
        'total_jobs_posted',
        'total_deals',
        'successful_deals',
        'total_workers_placed',
        'last_deal_at',
    ];

    protected function casts(): array
    {
        return [
            'phone_office'    => 'encrypted',
            'whatsapp_number' => 'encrypted',
            'is_verified'     => 'boolean',
            'verified_at'     => 'datetime',
            'last_deal_at'    => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AgentProfile $agentProfile) {
            if (empty($agentProfile->uuid)) {
                $agentProfile->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * পাবলিক URL-এ id এর বদলে uuid ব্যবহার হবে — /agents/{uuid}
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function jobPosts(): HasMany
    {
        return $this->hasMany(JobPost::class, 'posted_by_id', 'user_id');
    }

    public function activeJobPosts(): HasMany
    {
        return $this->jobPosts()->where('status', 'active');
    }

    /**
     * Step 8.2 — Leaderboard: agent হিসেবে সব job_deals (user_id ↔ agent_id)
     */
    public function dealsAsAgent(): HasMany
    {
        return $this->hasMany(JobDeal::class, 'agent_id', 'user_id');
    }
}