<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentAccount extends Model
{
    /**
     * এই মডেলে শুধু Admin Panel থেকেই কখনো write হয় — কোনো public form
     * বা Worker/Agent input এখানে সরাসরি bind হয় না, তাই $guarded = []
     * নিরাপদ (SkillCategory মডেলের মতো একই যুক্তি)।
     */
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function rechargeRequests(): HasMany
    {
        return $this->hasMany(RechargeRequest::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActiveForMethod(Builder $query, string $method): Builder
    {
        return $query
            ->where('payment_method', $method)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    public static function methodLabels(): array
    {
        return [
            'bank'   => 'ব্যাংক',
            'bkash'  => 'বিকাশ',
            'nagad'  => 'নগদ',
            'stcpay' => 'STC Pay',
        ];
    }
}