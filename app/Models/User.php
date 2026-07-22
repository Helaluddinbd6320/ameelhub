<?php

namespace App\Models;

use App\Models\AgentProfile;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

// BUG FIX (Helal-reported, Step 10.9 audit): User previously had no
// email-verification support at all — anyone could register with a
// fake/typo'd email and use the platform fully, since MustVerifyEmail
// was never implemented (Laravel's stock SendEmailVerificationNotification
// listener silently no-ops for users that don't implement this contract).
//
// Business decision: do NOT block login/panel access for unverified
// users — only specific sensitive actions (CV submit, Job post submit,
// Withdrawal request, Recharge request) are gated at the action level
// (see CvApprovalService / JobPost submit / WithdrawalService::request /
// RechargeService::request). Implementing MustVerifyEmailContract + the
// trait here is what makes hasVerifiedEmail() work and lets the
// Registered event automatically fire the verification email.
class User extends Authenticatable implements FilamentUser, MustVerifyEmailContract
{
    use HasFactory, Notifiable, HasRoles, MustVerifyEmail;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'facebook_id',
        'avatar',
        'referred_by_id',
        'phone',
    ];

    protected $guarded = [
        'role',
        'available_balance',
        'held_balance',
        'email_verified_at',
        'account_source',
        'claimed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'facebook_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'available_balance' => 'decimal:2',
            'held_balance'      => 'decimal:2',
            'phone'             => 'encrypted',
            'claimed_at'        => 'datetime',
        ];
    }

    // ─── Filament Panel Authorization ─────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'  => $this->hasAnyRole(['super_admin', 'admin', 'staff']),
            'agent'  => $this->hasRole('agent'),
            'worker' => $this->hasRole('worker'),
            default  => false,
        };
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function agentProfile(): HasOne
    {
        return $this->hasOne(AgentProfile::class);
    }

    public function worker(): HasOne
    {
        return $this->hasOne(Worker::class, 'worker_user_id');
    }

    public function submittedWorkers(): HasMany
    {
        return $this->hasMany(Worker::class, 'submitted_by_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function referralCode(): HasOne
    {
        return $this->hasOne(ReferralCode::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isWorker(): bool
    {
        return $this->role === 'worker';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin', 'staff']);
    }

    public function totalBalance(): float
    {
        return (float) $this->available_balance + (float) $this->held_balance;
    }

    public function isAgentCreatedUnclaimed(): bool
    {
        return $this->account_source === 'agent_created' && $this->claimed_at === null;
    }
}