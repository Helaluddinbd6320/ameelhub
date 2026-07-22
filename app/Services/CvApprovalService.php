<?php

namespace App\Services;

use App\Models\User;
use App\Models\Worker;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CvApprovalService
{
    public function __construct(
        protected WalletService $wallet,
        protected NotificationService $notifications,
    ) {}

    // ─────────────────────────────────────────────
    // Fee Deduction (Worker submits CV for approval)
    // ─────────────────────────────────────────────

    public function deductFee(Worker $worker): WalletTransaction
    {
        if ($worker->approval_fee_charged) {
            throw new RuntimeException('CV fee has already been charged for this worker.');
        }

        if (! $worker->worker_user_id) {
            throw new RuntimeException("Worker #{$worker->id} has no linked worker_user_id.");
        }

        $user = User::find($worker->worker_user_id);

        if (! $user) {
            Log::error('CvApprovalService::deductFee — linked user not found', [
                'worker_id'      => $worker->id,
                'worker_user_id' => $worker->worker_user_id,
            ]);
            throw new RuntimeException("Linked user account not found for Worker #{$worker->id}.");
        }

        // BUSINESS FIX (Helal-reported, Step 10.9 audit): block CV
        // submission (which charges the CV approval fee and sends it for
        // admin review) from users whose email is not yet verified — one
        // of the four sensitive actions gated per the email-verification
        // decision (CV submit, Job post submit, Withdrawal, Recharge).
        // Checked here rather than blocking login/panel access entirely.
        if (! $user->hasVerifiedEmail()) {
            throw new RuntimeException('CV জমা দেওয়ার আগে আপনার ইমেইল ভেরিফাই করতে হবে।');
        }

        $transaction = DB::transaction(function () use ($worker, $user) {
            // WalletService::deduct() নিজেই lockForUpdate() করে এবং
            // insufficient balance হলে WalletException ছোঁড়ে।
            $transaction = $this->wallet->chargeCvApprovalFee($user, $worker->id);

            // guarded ফিল্ড — forceFill বাধ্যতামূলক (mass-assignment silently ignore করবে না)
            $worker->forceFill([
                'approval_fee_charged' => true,
                'status'               => 'pending',
            ])->save();

            return $transaction;
        });

        // DB commit হওয়ার পরে notification পাঠানো হচ্ছে, যাতে transaction rollback
        // হলে ভুল notification না যায়।
        $this->notifications->cvSubmitted($worker->fresh());

        return $transaction;
    }

    // ─────────────────────────────────────────────
    // Approve (Admin action)
    // ─────────────────────────────────────────────

    public function approve(Worker $worker, User $admin): Worker
    {
        if ($worker->status !== 'pending') {
            throw new RuntimeException("Worker #{$worker->id} is not in pending status (current: {$worker->status}).");
        }

        DB::transaction(function () use ($worker, $admin) {
            $worker->forceFill([
                'status'         => 'active',
                'is_verified'    => true,
                'approved_by_id' => $admin->id,
                'approved_at'    => now(),
                'rejection_reason' => null,
            ])->save();
        });

        Log::channel('daily')->info('Worker CV approved', [
            'worker_id'  => $worker->id,
            'admin_id'   => $admin->id,
        ]);

        $fresh = $worker->fresh();

        $this->notifications->cvApproved($fresh);

        return $fresh;
    }

    // ─────────────────────────────────────────────
    // Reject (Admin action) — fee refund সহ
    // ─────────────────────────────────────────────

    public function reject(Worker $worker, string $reason, User $admin): Worker
    {
        if ($worker->status !== 'pending') {
            throw new RuntimeException("Worker #{$worker->id} is not in pending status (current: {$worker->status}).");
        }

        if (! $worker->worker_user_id) {
            throw new RuntimeException("Worker #{$worker->id} has no linked worker_user_id.");
        }

        $user = User::find($worker->worker_user_id);

        if (! $user) {
            Log::error('CvApprovalService::reject — linked user not found', [
                'worker_id'      => $worker->id,
                'worker_user_id' => $worker->worker_user_id,
            ]);
            throw new RuntimeException("Linked user account not found for Worker #{$worker->id}.");
        }

        DB::transaction(function () use ($worker, $reason, $admin, $user) {
            // ফি refund — শুধু যদি আসলেই চার্জ করা হয়ে থাকে
            if ($worker->approval_fee_charged) {
                $this->wallet->refundCvApprovalFee($user, $worker->id, $admin);
            }

            $worker->forceFill([
                'status'                => 'rejected',
                'rejection_reason'      => $reason,
                'approval_fee_charged'  => false, // পরবর্তী resubmit-এ আবার চার্জ হবে
                'is_verified'           => false,
            ])->save();
        });

        Log::channel('daily')->info('Worker CV rejected', [
            'worker_id' => $worker->id,
            'admin_id'  => $admin->id,
            'reason'    => $reason,
        ]);

        $fresh = $worker->fresh();

        $this->notifications->cvRejected($fresh, $reason);

        return $fresh;
    }

    // ─────────────────────────────────────────────
    // Standalone refund (এক্সপোজড আলাদা মেথড — যদি কখনো manual লাগে)
    // ─────────────────────────────────────────────

    public function refundFee(Worker $worker, User $admin): WalletTransaction
    {
        if (! $worker->approval_fee_charged) {
            throw new RuntimeException("Worker #{$worker->id} has no charged fee to refund.");
        }

        if (! $worker->worker_user_id) {
            throw new RuntimeException("Worker #{$worker->id} has no linked worker_user_id.");
        }

        $user = User::findOrFail($worker->worker_user_id);

        return DB::transaction(function () use ($worker, $user, $admin) {
            $transaction = $this->wallet->refundCvApprovalFee($user, $worker->id, $admin);

            $worker->forceFill([
                'approval_fee_charged' => false,
            ])->save();

            return $transaction;
        });
    }
}