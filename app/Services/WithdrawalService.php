<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\JobDeal;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalService
{
    public function __construct(
        protected WalletService $wallet,
        protected NotificationService $notifications
    ) {}

    // ─────────────────────────────────────────────
    // Request — User একটি withdrawal request পাঠায়
    // ─────────────────────────────────────────────

    /**
     * Withdrawal request তৈরি করে এবং তাৎক্ষণিকভাবে available balance থেকে
     * amount deduct করে (double-spend/overdraft এড়াতে)।
     * Admin পরে Approve করলে শুধু status বদলায়; Reject করলে টাকা ফেরত যায়।
     */
    public function request(
        User $user,
        float $amount,
        string $method,
        string $accountDetails
    ): WithdrawalRequest {
        // Section 1: Pre-transaction validations (fail fast, বাংলা মেসেজ সহ)
        // এগুলো শুধু dry-run/early-fail এর জন্য — লক নেওয়ার আগেই স্পষ্ট
        // ভ্যালিডেশন এরর ইউজারকে দ্রুত দেখানোর জন্য। প্রকৃত নিশ্চয়তার জন্য
        // balance ও daily-limit উভয়ই নিচে DB::transaction() এর ভেতরে,
        // lockForUpdate() নেওয়ার পরে আবার re-check হয় (TOCTOU race এড়াতে)।
        //
        // SECURITY FIX (Step 10.7e audit): assertWithinDailyLimit() আগে শুধু
        // এখানে, lock নেওয়ার আগে চেক হতো। দুইটা concurrent request (double-click
        // বা দুই ট্যাব) একই সাথে এই চেক পাস করে ফেলতে পারত lock নেওয়ার আগেই,
        // ফলে দৈনিক সীমা (৩টা) bypass হয়ে যেতে পারত। এখন এটা নিচে
        // DB::transaction() এর ভেতরে, user row lock করার পরে আবার re-check
        // হয় — ঠিক balance check এর মতোই।
        $this->wallet->validateWithdrawal($user, $amount);
        $this->assertNoActiveDispute($user);
        $this->assertWithinDailyLimit($user);

        $withdrawal = DB::transaction(function () use ($user, $amount, $method, $accountDetails) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            // Lock এর ভেতরেও re-check (race condition safety) —
            // balance এবং daily-limit দুটোই, যাতে দুইটা concurrent request
            // একই সাথে pre-check পাস করে দুটোই তৈরি হয়ে যেতে না পারে।
            if ($user->available_balance < $amount) {
                throw WalletException::insufficientBalance($amount, $user->available_balance);
            }

            $this->assertWithinDailyLimit($user);

            /** @var WithdrawalRequest $withdrawal */
            // SECURITY FIX (Step 10.7e audit): 'user_id' is now guarded on
            // the WithdrawalRequest model, so a plain create() would
            // silently drop it, failing the NOT NULL constraint (or worse,
            // if the column had a default, creating an orphaned/misattributed
            // record). forceCreate() bypasses mass-assignment protection
            // for this trusted, system-controlled insert.
            $withdrawal = WithdrawalRequest::forceCreate([
                'uuid'            => (string) Str::uuid(),
                'user_id'         => $user->id,
                'amount'          => $amount,
                'payment_method'  => $method,
                'account_details' => $accountDetails,
            ]);
            $withdrawal->forceFill(['status' => 'pending'])->save();

            // Available balance থেকে immediately deduct
            $this->wallet->chargeWithdrawal($user, $amount, $withdrawal->id, $user);

            return $withdrawal->fresh();
        });

        // DB commit হওয়ার পরে notification — transaction rollback হলে ভুল notify এড়াতে।
        $this->notifications->withdrawalRequested($withdrawal);

        return $withdrawal;
    }

    // ─────────────────────────────────────────────
    // Approve — Admin কর্তৃক
    // ─────────────────────────────────────────────

    public function approve(WithdrawalRequest $req, User $admin): WithdrawalRequest
    {
        $req = DB::transaction(function () use ($req, $admin) {
            $req = WithdrawalRequest::where('id', $req->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw WalletException::invalidWithdrawalStatus();
            }

            // টাকা request() এর সময়ই deduct হয়ে গেছে; এখানে শুধু status ফাইনালাইজ হয়
            $req->forceFill([
                'status'          => 'completed',
                'processed_by_id' => $admin->id,
                'processed_at'    => now(),
            ])->save();

            return $req->fresh();
        });

        $this->notifications->withdrawalApproved($req);

        return $req;
    }

    // ─────────────────────────────────────────────
    // Reject — Admin কর্তৃক, reason সহ, টাকা ফেরত
    // ─────────────────────────────────────────────

    public function reject(WithdrawalRequest $req, string $reason, User $admin): WithdrawalRequest
    {
        $req = DB::transaction(function () use ($req, $reason, $admin) {
            $req = WithdrawalRequest::where('id', $req->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw WalletException::invalidWithdrawalStatus();
            }

            $req->forceFill([
                'status'          => 'rejected',
                'admin_note'      => $reason,
                'processed_by_id' => $admin->id,
                'processed_at'    => now(),
            ])->save();

            // Deducted টাকা ফেরত (available balance এ credit)
            $this->wallet->credit(
                $req->user,
                (float) $req->amount,
                'manual_adjustment',
                "Withdrawal request #{$req->id} প্রত্যাখ্যাত — টাকা ফেরত। কারণ: {$reason}",
                'withdrawal_requests',
                $req->id,
                $admin
            );

            return $req->fresh();
        });

        $this->notifications->withdrawalRejected($req, $reason);

        return $req;
    }

    // ─────────────────────────────────────────────
    // Private Guards
    // ─────────────────────────────────────────────

    /**
     * User (worker হিসেবে অথবা agent হিসেবে) কোনো disputed deal এ থাকলে
     * withdrawal ব্লক করে।
     */
    protected function assertNoActiveDispute(User $user): void
    {
        $isAgentDisputed = JobDeal::query()
            ->where('agent_id', $user->id)
            ->where('status', 'disputed')
            ->exists();

        if ($isAgentDisputed) {
            throw WalletException::activeDisputeBlock();
        }

        $worker = Worker::where('worker_user_id', $user->id)->first();

        if ($worker) {
            $isWorkerDisputed = JobDeal::query()
                ->where('worker_id', $worker->id)
                ->where('status', 'disputed')
                ->exists();

            if ($isWorkerDisputed) {
                throw WalletException::activeDisputeBlock();
            }
        }
    }

    /** দৈনিক সর্বোচ্চ withdrawal request সীমা (default: 3) */
    protected function assertWithinDailyLimit(User $user): void
    {
        $limit = (int) setting('withdrawal_daily_limit', 3);

        $todayCount = WithdrawalRequest::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $limit) {
            throw WalletException::dailyWithdrawalLimitExceeded($limit);
        }
    }
}