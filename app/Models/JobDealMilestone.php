<?php

namespace App\Models;

use App\Models\DisputeEvidence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobDealMilestone extends Model
{
    /**
     * SECURITY FIX (Step 10.7 audit):
     * এই মডেলে আগে কোনো $guarded ছিল না — অথচ এটাই পুরো escrow release
     * chain এর সবচেয়ে sensitive অংশ নিয়ন্ত্রণ করে। MilestoneService এবং
     * DisputeService বর্তমানে সঠিকভাবে forceFill() ব্যবহার করছে, তাই আজ
     * পর্যন্ত কোনো exploit ঘটেনি — কিন্তু unguarded থাকায় ভবিষ্যতে কোনো
     * Livewire/Filament action সরাসরি update() করলে একজন agent নিজেই
     * নিজের milestone status 'agent_confirmed' বানিয়ে ফেলতে পারত worker
     * confirm ছাড়াই, এবং admin release (যেটা শুধু status চেক করে) সেই
     * fake confirmation এর ভিত্তিতে টাকা ছেড়ে দিত।
     *
     * এখন শুধু creation-time ডেটা (percentage/amount/commission হিসাব
     * ইত্যাদি) mass-assignable — বাকি সব lifecycle/dispute/resolution
     * ফিল্ড guarded, শুধু forceFill()->save() দিয়ে MilestoneService বা
     * DisputeService থেকেই লেখা যাবে।
     *
     * ⚠️ NOTE: MilestoneService::createForDeal() এ JobDealMilestone::create()
     * কলের ভেতরে 'status' => 'pending' পাস করা হয় — এখন 'status' guarded
     * হওয়ায় সেই create() কল অবশ্যই forceCreate() এ বদলাতে হবে, নাহলে
     * status field silently drop হয়ে DB default (যদি থাকে) বা NULL/error
     * এ গিয়ে পড়বে।
     */
    protected $fillable = [
        'job_deal_id',
        'milestone_number',
        'title',
        'description',
        'percentage',
        'amount_sar',
        'commission_sar',
        'agent_receives_sar',
    ];

    /**
     * Lifecycle / confirmation / dispute / resolution fields — system-only.
     * Write via forceFill()->save() from MilestoneService / DisputeService ONLY.
     */
    protected $guarded = [
        'status',
        'worker_confirmed_at',
        'agent_confirmed_at',
        'admin_released_at',
        'released_by_id',
        'dispute_raised_by',
        'dispute_reason',
        'dispute_raised_at',
        'resolution',
        'resolution_notes',
        'resolved_by_id',
        'resolved_at',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'percentage'          => 'decimal:2',
            'amount_sar'          => 'decimal:2',
            'commission_sar'      => 'decimal:2',
            'agent_receives_sar'  => 'decimal:2',
            'worker_confirmed_at' => 'datetime',
            'agent_confirmed_at'  => 'datetime',
            'admin_released_at'   => 'datetime',
            'dispute_raised_at'   => 'datetime',
            'resolved_at'         => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function deal(): BelongsTo
    {
        return $this->belongsTo(JobDeal::class, 'job_deal_id');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class, 'milestone_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isReleased(): bool
    {
        return $this->status === 'admin_released';
    }

    public function isDisputed(): bool
    {
        return $this->status === 'disputed';
    }
}