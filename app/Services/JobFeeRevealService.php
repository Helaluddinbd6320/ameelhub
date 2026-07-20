<?php

namespace App\Services;

use App\Models\JobFeeReveal;
use App\Models\JobPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobFeeRevealService
{
    public function __construct(protected WalletService $walletService)
    {
    }

    public function hasRevealed(JobPost $jobPost, User $user): bool
    {
        return JobFeeReveal::where('user_id', $user->id)
            ->where('job_post_id', $jobPost->id)
            ->exists();
    }

    public function getReveal(JobPost $jobPost, User $user): ?JobFeeReveal
    {
        return JobFeeReveal::where('user_id', $user->id)
            ->where('job_post_id', $jobPost->id)
            ->first();
    }

    /**
     * Job এর Agent Fee reveal করে — প্রথমবার হলে wallet কাটে,
     * আগে reveal করা থাকলে সেই রেকর্ডই ফেরত দেয় (idempotent)।
     */
    public function reveal(JobPost $jobPost, User $user): JobFeeReveal
    {
        return DB::transaction(function () use ($jobPost, $user) {
            // SECURITY FIX (Step 10.7f audit — TOCTOU race, same pattern as
            // ContactRevealService): JobPost রো লক করে concurrent reveal
            // attempt সিরিয়ালাইজ করা হচ্ছে, তারপর লকের ভেতরে আবার
            // existing-check — যাতে দুটো concurrent request একই সাথে
            // "নেই" দেখে দুটোই চার্জ+insert করে ফেলতে না পারে।
            $lockedJobPost = JobPost::where('id', $jobPost->id)->lockForUpdate()->firstOrFail();

            $existing = $this->getReveal($jobPost, $user);
            if ($existing) {
                return $existing;
            }

            // SECURITY FIX (Step 10.7f audit): HTTP route এর
            // 'throttle:fee-reveal' middleware (20/day) ব্যবহারই হয় না,
            // কারণ এই ফিচার Livewire component method call দিয়ে চলে
            // (routes/web.php bypass হয়ে যায়)। তাই এখানে সমতুল্য
            // service-layer daily-limit যোগ করা হলো — ContactRevealService
            // এ যেভাবে করা হয়েছে ঠিক সেভাবেই।
            $this->assertWithinDailyRevealLimit($user);

            $cost = (float) $lockedJobPost->fee_reveal_cost;

            $this->walletService->chargeJobFeeReveal($user, $jobPost->id, $cost);

            return JobFeeReveal::create([
                'user_id'        => $user->id,
                'job_post_id'    => $jobPost->id,
                'amount_charged' => $cost,
                'ip_address'     => request()->ip(),
                'revealed_at'    => now(),
            ]);
        });
    }

    /**
     * দৈনিক সর্বোচ্চ fee-reveal সীমা (নতুন/চার্জযোগ্য reveal এর ক্ষেত্রে —
     * আগেই reveal করা job আবার দেখা ফ্রি এবং এই কাউন্টে ধরা হয় না, কারণ
     * সেটা reveal() এর শুরুতেই idempotent early-return হিসেবে হ্যান্ডেল হয়)।
     * settings key: fee_reveal_daily_limit (ডিফল্ট 20, blueprint অনুযায়ী)।
     */
    private function assertWithinDailyRevealLimit(User $user): void
    {
        $limit = (int) setting('fee_reveal_daily_limit', 20);

        $todayCount = JobFeeReveal::where('user_id', $user->id)
            ->whereDate('revealed_at', now()->toDateString())
            ->count();

        if ($todayCount >= $limit) {
            throw ValidationException::withMessages([
                'job_post_id' => "আজকের জন্য ফি রিভিলের সীমা ({$limit}টি) শেষ হয়ে গেছে। আগামীকাল আবার চেষ্টা করুন।",
            ]);
        }
    }
}