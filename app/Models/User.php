<?php

namespace App\Models;

use App\Models\AgentProfile;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * SECURITY FIX (Step 10.7 audit):
     * আগে এই মডেলে $fillable তে role/available_balance/held_balance/
     * email_verified_at/account_source/claimed_at ছিল এবং কোনো $guarded
     * ছিল না — অর্থাৎ প্রতিটি field mass-assignable ছিল। এটা privilege-
     * escalation (নিজেকে admin বানানো) এবং wallet-fraud (নিজের balance
     * নিজে বাড়ানো) দুটোরই সরাসরি রিস্ক ছিল।
     *
     * এখন শুধু genuinely user-editable / registration-time ফিল্ডগুলো
     * fillable — বাকি সব $guarded এ, শুধু forceFill()/forceCreate() দিয়ে
     * trusted service/observer/admin-action থেকে লেখা যাবে।
     */
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

    /**
     * Admin/system-only fields — cannot be mass-assigned.
     * Write via forceFill()->save() (Admin "Change Role"/"Adjust Wallet"
     * actions, WalletService, UserObserver, email-verification flow,
     * agent-account "claim" flow) ONLY.
     */
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

    /**
     * SECURITY FIX (Step 10.7d audit — CRITICAL):
     * This method was entirely missing. Filament's PanelProvider files
     * only had ->authorization(...) commented out (correctly, since that
     * method doesn't exist in Filament v5), but nothing replaced it.
     * Without a User model implementing FilamentUser::canAccessPanel(),
     * Filament defaults to ALLOWING every authenticated user into every
     * panel — meaning a worker could log into /admin, or an agent into
     * /worker, with zero role restriction. This closes that gap.
     */
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

    /**
     * True when this account was auto-created by an Agent submitting a
     * Worker CV, and the worker has not yet claimed it (set their own
     * email/password via the future claim flow — Phase 9/10 TODO).
     */
    public function isAgentCreatedUnclaimed(): bool
    {
        return $this->account_source === 'agent_created' && $this->claimed_at === null;
    }
}