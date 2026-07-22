<?php

namespace App\Services;

use App\Models\RechargeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RechargeService
{
    public function __construct(
        protected WalletService $wallet,
        protected NotificationService $notifications
    ) {}

    // ─────────────────────────────────────────────
    // Request — User একটি recharge request পাঠায়
    // (bank/bKash/Nagad এ টাকা পাঠানোর পর, reference + proof সহ)
    // ─────────────────────────────────────────────

    /**
     * NOTE: Withdrawal থেকে ভিন্ন — এখানে request করার সাথে সাথে wallet এ
     * কোনো টাকা যোগ হয় না। টাকা এখনো সত্যিকারের ব্যাংক/বিকাশ অ্যাকাউন্টে
     * পৌঁছায়নি বলে দাবি করা হচ্ছে মাত্র — Admin ম্যানুয়ালি ব্যাংক/বিকাশ
     * স্টেটমেন্ট মিলিয়ে approve করলে তবেই WalletService::credit() কল হয়।
     * তাই এখানে DB::transaction() + lockForUpdate() লাগছে না (কোনো balance
     * mutate হচ্ছে না), শুধু forceCreate() যথেষ্ট consistency এর জন্য।
     */
    public function request(
        User $user,
        float $amount,
        string $method,
        ?string $referenceNumber,
        ?string $proofFilePath
    ): RechargeRequest {
        // BUSINESS FIX (Helal-reported, Step 10.9 audit): block recharge
        // requests from users whose email is not yet verified — one of the
        // four sensitive actions gated per the email-verification decision
        // (CV submit, Job post submit, Withdrawal, Recharge). Login/panel
        // browsing intentionally stays open for unverified users.
        if (! $user->hasVerifiedEmail()) {
            throw new \RuntimeException('Recharge request পাঠানোর আগে আপনার ইমেইল ভেরিফাই করতে হবে।');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('পরিমাণ শূন্যের বেশি হতে হবে।');
        }

        $minAmount = (float) setting('min_recharge_sar', 10);
        if ($amount < $minAmount) {
            throw new \InvalidArgumentException("সর্বনিম্ন রিচার্জ {$minAmount} SAR।");
        }

        $this->assertWithinDailyLimit($user);

        /** @var RechargeRequest $recharge */
        // SECURITY: user_id guarded on the model — forceCreate() needed,
        // same reasoning as WithdrawalRequest::forceCreate() in WithdrawalService.
        $recharge = RechargeRequest::forceCreate([
            'uuid'             => (string) Str::uuid(),
            'user_id'          => $user->id,
            'amount'           => $amount,
            'payment_method'   => $method,
            'reference_number' => $referenceNumber,
            'proof_file'       => $proofFilePath,
        ]);
        $recharge->forceFill(['status' => 'pending'])->save();

        $this->notifications->rechargeRequested($recharge->fresh());

        return $recharge->fresh();
    }

    // ─────────────────────────────────────────────
    // Approve — Admin কর্তৃক, তখনই wallet এ credit হয়
    // ─────────────────────────────────────────────

    public function approve(RechargeRequest $req, User $admin): RechargeRequest
    {
        $req = DB::transaction(function () use ($req, $admin) {
            $req = RechargeRequest::where('id', $req->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new \RuntimeException('এই request ইতিমধ্যে প্রসেস করা হয়েছে।');
            }

            $req->forceFill([
                'status'          => 'approved',
                'processed_by_id' => $admin->id,
                'processed_at'    => now(),
            ])->save();

            // এখন actual wallet credit — WalletService নিজেই User row
            // lockForUpdate() করে balance বাড়ায় (DB::transaction() nested-safe)।
            $this->wallet->credit(
                $req->user,
                (float) $req->amount,
                'wallet_recharge',
                "Recharge request #{$req->id} অনুমোদিত ({$req->payment_method}, ref: {$req->reference_number})",
                'recharge_requests',
                $req->id,
                $admin
            );

            return $req->fresh();
        });

        $this->notifications->rechargeApproved($req);

        return $req;
    }

    // ─────────────────────────────────────────────
    // Reject — Admin কর্তৃক, reason সহ (কোনো wallet পরিবর্তন হয় না)
    // ─────────────────────────────────────────────

    public function reject(RechargeRequest $req, string $reason, User $admin): RechargeRequest
    {
        $req = DB::transaction(function () use ($req, $reason, $admin) {
            $req = RechargeRequest::where('id', $req->id)->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new \RuntimeException('এই request ইতিমধ্যে প্রসেস করা হয়েছে।');
            }

            $req->forceFill([
                'status'          => 'rejected',
                'admin_note'      => $reason,
                'processed_by_id' => $admin->id,
                'processed_at'    => now(),
            ])->save();

            return $req->fresh();
        });

        $this->notifications->rechargeRejected($req, $reason);

        return $req;
    }

    // ─────────────────────────────────────────────
    // Private Guards
    // ─────────────────────────────────────────────

    /** স্প্যাম/ভুয়া request এড়াতে দৈনিক সীমা (default: 5) */
    protected function assertWithinDailyLimit(User $user): void
    {
        $limit = (int) setting('recharge_daily_limit', 5);

        $todayCount = RechargeRequest::query()
            ->where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $limit) {
            throw new \RuntimeException("আজকের জন্য সর্বোচ্চ {$limit}টি recharge request পাঠানো যাবে।");
        }
    }
}