<?php

namespace App\Models;

use App\Models\JobInterest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPost extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'posted_by_id',
        'job_title',
        'job_title_ar',
        'employer_name',
        'employer_type',
        'employer_city',
        'employer_country',
        'skill_category_id',
        'skill_sub_details',
        'vacancies',
        'salary_sar',
        'accommodation',
        'food_included',
        'transport_provided',
        'contract_months',
        'working_hours',
        'weekly_off',
        'overtime_available',
        'description',
        'requirements',
        'agent_fee_sar',
        'fee_reveal_cost',
        'status',
        'expires_at',
        'close_reason',
    ];

    /**
     * Admin-only fields — cannot be mass-assigned
     */
    protected $guarded = [
        'approved_by_id',
        'approved_at',
        'filled_count',
        'view_count',
        'closed_by_id',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'agent_fee_sar'      => 'encrypted',
            'accommodation'      => 'boolean',
            'food_included'      => 'boolean',
            'transport_provided' => 'boolean',
            'overtime_available' => 'boolean',
            'salary_sar'         => 'decimal:2',
            'fee_reveal_cost'    => 'decimal:2',
            'approved_at'        => 'datetime',
            'closed_at'          => 'datetime',
            'expires_at'         => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function skillCategory(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class);
    }

    public function interests(): HasMany
    {
        return $this->hasMany(JobInterest::class);
    }

    public function feeReveals(): HasMany
    {
        return $this->hasMany(JobFeeReveal::class);
    }

    public function noks(): HasMany
    {
        return $this->hasMany(AgentNok::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(JobSelection::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(JobDeal::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isVisible(): bool
    {
        return $this->status === 'active'
            && $this->filled_count < $this->vacancies
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function remainingVacancies(): int
    {
        return max(0, $this->vacancies - $this->filled_count);
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/jobs/' . $this->uuid);
    }
}