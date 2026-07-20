<?php

namespace App\Services;

use App\Models\JobDeal;
use App\Models\ReferralCode;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralService
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notifications
    ) {
    }

    /**
     * নতুন ইউজারের জন্য ইউনিক রেফারেল কোড তৈরি করে।
     * UserObserver::created() থেকে সব রোলের (worker + agent) জন্য কল হয়।
     */
    public function generateCode(User $user): ReferralCode
    {
        return ReferralCode::firstOrCreate(
            ['user_id' => $user->id],
            ['code' => $this->generateUniqueCode(), 'created_at' => now()]
        );
    }

    /**
     * রেজিস্ট্রেশনের সময় ?ref={code} থাকলে RegisterController/Livewire থেকে কল হয়।
     * referred_by_id সেট করে এবং একটি pending reward রেকর্ড তৈরি করে।
     * বোনাস এখনই দেওয়া হয় না — referee এর প্রথম deal completed হলে
     * checkAndPayBonusForDeal() বোনাস pay করবে।
     * অবৈধ/নিজের কোড হলে চুপচাপ ইগনোর করা হয় — রেজিস্ট্রেশন কখনো আটকাবে না।
     */
    public function processRegistration(User $newUser, ?string $code): void
    {
        if (blank($code)) {
            return;
        }

        $code = strtoupper(trim($code));
        $referralCode = ReferralCode::where('code', $code)->first();

        if (! $referralCode) {
            Log::warning('Invalid referral code at registration', [
                'code' => $code, 'new_user_id' => $newUser->id,
            ]);
            return;
        }

        $referrer = $referralCode->user;

        // Self-referral guard — নিজের কোড নিজে ব্যবহার করা যাবে না
        if (! $referrer || $referrer->id === $newUser->id) {
            Log::warning('Self-referral attempt blocked at registration', [
                'user_id' => $newUser->id,
            ]);
            return;
        }

        DB::transaction(function () use ($newUser, $referrer, $referralCode) {
            $newUser->forceFill(['referred_by_id' => $referrer->id])->save();

            $referralCode->increment('used_count');

            // referee_id UNIQUE — একজন referee-র জন্য একটাই reward রেকর্ড
            ReferralReward::firstOrCreate(
                ['referee_id' => $newUser->id],
                [
                    'referrer_id'   => $referrer->id,
                    'reward_amount' => (float) setting('referral_bonus_sar', 20),
                    'status'        => 'pending',
                    'created_at'    => now(),
                ]
            );
        });
    }

    /**
     * একটি Deal completed হলে কল হয় (MilestoneService::releaseByAdmin এর
     * milestone_number === 3 ব্রাঞ্চ থেকে)। এই deal-এ জড়িত worker ও agent
     * — দুজনের জন্যই আলাদাভাবে চেক করে: কারো referrer থাকলে এবং এটাই তাদের
     * প্রথম completed deal হলে, referrer-কে বোনাস pay করে।
     */
    public function checkAndPayBonusForDeal(JobDeal $deal): void
    {
        $deal->loadMissing(['worker', 'agent']);

        if ($deal->worker && $deal->worker->worker_user_id) {
            $this->tryPayForReferee((int) $deal->worker->worker_user_id, $deal, 'worker');
        }

        if ($deal->agent_id) {
            $this->tryPayForReferee((int) $deal->agent_id, $deal, 'agent');
        }
    }

    /**
     * নির্দিষ্ট referee (worker_user_id বা agent user id) এর জন্য বোনাস pay
     * করার চেষ্টা করে — first-deal শর্ত ও ফ্রড-গার্ড যাচাই করে।
     */
    private function tryPayForReferee(int $refereeUserId, JobDeal $deal, string $role): void
    {
        $reward = ReferralReward::where('referee_id', $refereeUserId)
            ->where('status', 'pending')
            ->first();

        if (! $reward) {
            return; // এই ইউজার কারো রেফারেলে আসেনি, অথবা ইতিমধ্যে paid হয়ে গেছে
        }

        // ফ্রড গার্ড: যে Agent এই referee-কে রেফার করেছে, সেই Agent-ই যদি এই
        // deal-এর agent হয় (নিজের রেফার করা worker-কে নিজেই deal দিয়ে বোনাস
        // বানানোর চেষ্টা) — বোনাস আটকে রাখা হবে, admin review এর জন্য লগ হবে।
        if ((int) $deal->agent_id === (int) $reward->referrer_id) {
            Log::warning('Referral bonus withheld — referrer is the deal agent (self-dealing suspected)', [
                'reward_id'   => $reward->id,
                'deal_id'     => $deal->id,
                'referrer_id' => $reward->referrer_id,
                'role'        => $role,
            ]);
            return;
        }

        // Self-referral ডিফেন্সিভ গার্ড
        if ((int) $reward->referrer_id === $refereeUserId) {
            Log::warning('Referral bonus withheld — self-referral detected', ['reward_id' => $reward->id]);
            return;
        }

        // "প্রথম completed deal" শর্ত — সংখ্যা ঠিক ১ না হলে বোনাস দেওয়া হবে না
        if ($this->completedDealsCountForUser($refereeUserId, $role) !== 1) {
            return;
        }

        $this->payBonus($reward);
    }

    /**
     * role অনুযায়ী সঠিক কলাম দিয়ে ইউজারের completed deal সংখ্যা গোনে
     * (worker হলে workers.worker_user_id দিয়ে, agent হলে job_deals.agent_id দিয়ে)।
     */
    private function completedDealsCountForUser(int $userId, string $role): int
    {
        if ($role === 'agent') {
            return JobDeal::where('agent_id', $userId)
                ->where('status', 'completed')
                ->count();
        }

        return JobDeal::where('status', 'completed')
            ->whereHas('worker', fn ($q) => $q->where('worker_user_id', $userId))
            ->count();
    }

    /**
     * চূড়ান্তভাবে বোনাস pay করে — WalletService::creditReferralBonus() কল করে
     * এবং reward status = paid মার্ক করে। lockForUpdate দিয়ে race-condition
     * এ ডাবল-পে ঠেকায় (idempotency)।
     *
     * NOTE: notification transaction commit হওয়ার *পরে* পাঠানো হয় (rollback
     * হলে ভুল notify এড়াতে) — তাই transaction এর ভেতরে শুধু referrer +
     * reward_amount বের করে রিটার্ন করা হয়, notify বাইরে থেকে হয়।
     */
    private function payBonus(ReferralReward $reward): void
    {
        $result = DB::transaction(function () use ($reward) {
            $locked = ReferralReward::where('id', $reward->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                return null; // race condition — অন্য প্রসেস ইতিমধ্যে pay করে ফেলেছে
            }

            $referrer = User::find($locked->referrer_id);

            if (! $referrer) {
                Log::error('Referral bonus payment failed — referrer not found', ['reward_id' => $locked->id]);
                return null;
            }

            $this->walletService->creditReferralBonus($referrer, $locked->id);

            $locked->forceFill([
                'status'  => 'paid',
                'paid_at' => now(),
            ])->save();

            return [
                'referrer' => $referrer,
                'amount'   => (float) $locked->reward_amount,
            ];
        });

        if ($result) {
            $this->notifications->referralBonusPaid($result['referrer'], $result['amount']);
        }
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (ReferralCode::where('code', $code)->exists());

        return $code;
    }
}