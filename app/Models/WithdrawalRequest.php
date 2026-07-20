<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    /**
     * SECURITY FIX (Step 10.7e audit):
     * আগে কোনো $guarded ছিল না — status, admin_note, processed_by_id,
     * processed_at, এমনকি user_id পর্যন্ত সব fillable ছিল। WithdrawalService
     * সব জায়গায় সঠিকভাবে forceFill()/explicit array ব্যবহার করছে বলে আজ
     * পর্যন্ত exploit হয়নি, কিন্তু unguarded থাকায় ভবিষ্যতে কোনো Livewire
     * ফর্ম সরাসরি update() কল করলে একজন user নিজের rejected withdrawal
     * নিজেই 'completed' বানিয়ে ফেলতে পারত, অথবা user_id বদলে অন্যের নামে
     * withdrawal বসাতে পারত।
     *
     * এখানে শুধু $guarded প্যাটার্ন ব্যবহার করা হলো ($fillable বাদ দেওয়া
     * হয়েছে) — দুটো একসাথে define করলে $fillable থাকা field এ $guarded
     * silently ignore হয়ে যায় (এই একই anti-pattern আগে Worker/JobPost
     * মডেলেও পাওয়া গিয়েছিল)। এখন uuid/amount/payment_method/
     * account_details ডিফল্টভাবে mass-assignable (non-guarded), বাকি সব
     * guarded।
     *
     * ⚠️ NOTE: 'user_id' এখন guarded হওয়ায়, WithdrawalService::request()
     * এর ভেতরে WithdrawalRequest::create([..., 'user_id' => ..., ...])
     * কলটি অবশ্যই forceCreate() এ বদলাতে হবে — নাহলে user_id silently
     * drop হয়ে NOT NULL constraint এ DB error দেবে।
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
            'account_details' => 'encrypted',
            'amount'          => 'decimal:2',
            'processed_at'    => 'datetime',
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