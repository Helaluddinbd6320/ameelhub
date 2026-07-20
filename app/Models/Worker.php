<?php

namespace App\Models;

use App\Models\ContactReveal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Worker extends Model
{
    use SoftDeletes;

    /**
     * SECURITY FIX (Step 10.7 audit):
     * ১) আগে এই মডেলে $fillable এবং $guarded দুটোই define করা ছিল।
     *    Laravel এ isFillable() চেক করার সময় কোনো field $fillable এ
     *    থাকলে সেটা সাথে সাথেই allow হয়ে যায় — $guarded তখন চেক-ই হয়
     *    না। এখন কোনো conflict ছিল না বলে সমস্যা হয়নি, কিন্তু ভবিষ্যতে
     *    কেউ ভুলবশত কোনো guarded field (যেমন approved_at) $fillable এ
     *    যোগ করে দিলে সেটা নীরবে mass-assignable হয়ে যেত। তাই এখন
     *    AgentProfile-এর মতো শুধু $guarded প্যাটার্নে switch করা হলো —
     *    ভবিষ্যতে নতুন কলাম যোগ হলে ডিফল্টভাবে সুরক্ষিত থাকবে।
     * ২) cv_notes (Admin-only internal note) এবং rejection_reason
     *    (শুধু Admin সেট করে, worker শুধু দেখতে পারে) আগে fillable ছিল,
     *    guarded ছিল না। এখন দুটোই guarded করা হলো।
     *
     * Write path অপরিবর্তিত: CvApprovalService ইতিমধ্যেই forceFill()
     * ব্যবহার করে এই সব guarded field সেট করে, তাই কোনো service কোড
     * বদলানোর দরকার নেই।
     *
     * ⚠️ NOTE: Worker CV auto-creation (Blueprint Section 13, UserObserver)
     * এ Worker::create([..., 'status' => 'draft', ...]) কল করা হয় —
     * 'status' guarded হওয়ায় এই কলও অবশ্যই forceCreate() এ বদলাতে হবে।
     */
    protected $guarded = [
        'is_verified',
        'is_featured',
        'featured_until',
        'approval_fee_charged',
        'approved_by_id',
        'approved_at',
        'view_count',
        'status',
        'cv_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            // Encrypted fields
            'emergency_contact_phone' => 'encrypted',
            'passport_number'         => 'encrypted',
            'nid_number'              => 'encrypted',
            'iqama_number'            => 'encrypted',
            'phone_primary'           => 'encrypted',
            'phone_whatsapp'          => 'encrypted',
            'phone_saudi'             => 'encrypted',
            'email_personal'          => 'encrypted',
            // Booleans
            'driving_license'         => 'boolean',
            'is_in_saudi'             => 'boolean',
            'transfer_possible'       => 'boolean',
            'medical_fit'             => 'boolean',
            'is_verified'             => 'boolean',
            'is_featured'             => 'boolean',
            'approval_fee_charged'    => 'boolean',
            // Dates
            'date_of_birth'           => 'date',
            'passport_issue_date'     => 'date',
            'passport_expiry'         => 'date',
            'iqama_expiry'            => 'date',
            'available_from'          => 'date',
            'featured_until'          => 'date',
            'approved_at'             => 'datetime',
            // Decimal
            'expected_salary_sar'     => 'decimal:2',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function workerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_user_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(JobInterest::class);
    }

    public function noks(): HasMany
    {
        return $this->hasMany(AgentNok::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(JobDeal::class);
    }

    public function contactReveals(): HasMany
    {
        return $this->hasMany(ContactReveal::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    /**
     * Only CVs allowed to appear on public pages (Step 2.5 / 2.6).
     * NEVER include draft, pending, inactive, hired, rejected here.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['active', 'featured']);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isIqamaExpiringSoon(): bool
    {
        if (!$this->iqama_expiry) return false;
        return $this->iqama_expiry->diffInDays(now()) <= 30;
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/workers/' . $this->uuid);
    }
}