<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    // ─────────────────────────────────────────────
    // Transaction Type Constants
    // (wallet_transactions.type কলামের ভ্যালু — hardcoded string এড়াতে
    //  এবং Section 8 ব্লুপ্রিন্টের সব টাইপের সাথে মিল রাখতে)
    // ─────────────────────────────────────────────
    public const TYPE_WALLET_RECHARGE      = 'wallet_recharge';
    public const TYPE_CV_APPROVAL_FEE      = 'cv_approval_fee';
    public const TYPE_CV_REJECTION_REFUND  = 'cv_rejection_refund';
    public const TYPE_JOB_FEE_REVEAL       = 'job_fee_reveal';
    public const TYPE_CONTACT_REVEAL       = 'contact_reveal';
    public const TYPE_ESCROW_HOLD          = 'escrow_hold';
    public const TYPE_ESCROW_RELEASE_AGENT = 'escrow_release_agent';
    public const TYPE_ESCROW_DEDUCT_WORKER = 'escrow_deduct_worker';
    public const TYPE_ESCROW_REFUND        = 'escrow_refund';
    public const TYPE_REFERRAL_BONUS       = 'referral_bonus';
    public const TYPE_NOK_FEE              = 'nok_fee';
    public const TYPE_WITHDRAWAL_DEBIT     = 'withdrawal_debit';
    public const TYPE_MANUAL_ADJUSTMENT    = 'manual_adjustment';

    // ─────────────────────────────────────────────
    // SECTION 1 — Available Balance Operations
    // ─────────────────────────────────────────────

    /**
     * Available balance থেকে টাকা কাটো
     * ব্যবহার: CV approval fee, fee reveal, contact reveal
     */
    public function deduct(
        User $user,
        float $amount,
        string $type,
        string $description,
        ?string $refType = null,
        ?int $refId = null,
        ?User $createdBy = null
    ): WalletTransaction {
        $this->validateAmount($amount);

        $result = DB::transaction(function () use ($user, $amount, $type, $description, $refType, $refId, $createdBy) {
            // lockForUpdate: concurrent request থেকে রক্ষা করে
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($user->available_balance < $amount) {
                throw WalletException::insufficientBalance($amount, $user->available_balance);
            }

            $availableBefore = $user->available_balance;
            $heldBefore      = $user->held_balance;

            $user->decrement('available_balance', $amount);

            $transaction = $this->log([
                'user_id'          => $user->id,
                'type'             => $type,
                'amount'           => $amount,
                'direction'        => 'debit',
                'balance_type'     => 'available',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore - $amount,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore,
                'reference_type'   => $refType,
                'reference_id'     => $refId,
                'description'      => $description,
                'created_by_id'    => $createdBy?->id,
            ]);

            return [
                'transaction'      => $transaction,
                'user'             => $user,
                'availableBefore'  => (float) $availableBefore,
                'availableAfter'   => (float) ($availableBefore - $amount),
            ];
        });

        // DB commit হওয়ার পরে balance-crossing চেক — rollback হলে ভুল notify এড়াতে।
        $this->checkAndNotifyLowBalance($result['user'], $result['availableBefore'], $result['availableAfter']);

        return $result['transaction'];
    }

    /**
     * Available balance এ টাকা যোগ করো
     * ব্যবহার: CV rejection refund, escrow refund, referral bonus, admin manual credit
     */
    public function credit(
        User $user,
        float $amount,
        string $type,
        string $description,
        ?string $refType = null,
        ?int $refId = null,
        ?User $createdBy = null
    ): WalletTransaction {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($user, $amount, $type, $description, $refType, $refId, $createdBy) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $availableBefore = $user->available_balance;
            $heldBefore      = $user->held_balance;

            $user->increment('available_balance', $amount);

            return $this->log([
                'user_id'          => $user->id,
                'type'             => $type,
                'amount'           => $amount,
                'direction'        => 'credit',
                'balance_type'     => 'available',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore + $amount,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore,
                'reference_type'   => $refType,
                'reference_id'     => $refId,
                'description'      => $description,
                'created_by_id'    => $createdBy?->id,
            ]);
        });
    }

    // ─────────────────────────────────────────────
    // SECTION 2 — Escrow Operations
    // ─────────────────────────────────────────────

    /**
     * Available → Held: Escrow এ আটকে রাখো
     * ব্যবহার: Worker deal accept করলে
     */
    public function hold(
        User $user,
        float $amount,
        int $dealId,
        string $description = ''
    ): WalletTransaction {
        $this->validateAmount($amount);

        $result = DB::transaction(function () use ($user, $amount, $dealId, $description) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($user->available_balance < $amount) {
                throw WalletException::insufficientBalance($amount, $user->available_balance);
            }

            $availableBefore = $user->available_balance;
            $heldBefore      = $user->held_balance;

            $user->decrement('available_balance', $amount);
            $user->increment('held_balance', $amount);

            $transaction = $this->log([
                'user_id'          => $user->id,
                'type'             => 'escrow_hold',
                'amount'           => $amount,
                'direction'        => 'debit',
                'balance_type'     => 'available_to_held',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore - $amount,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore + $amount,
                'reference_type'   => 'job_deals',
                'reference_id'     => $dealId,
                'description'      => $description ?: "Deal #{$dealId} — Escrow hold",
                'created_by_id'    => null,
            ]);

            return [
                'transaction'      => $transaction,
                'user'             => $user,
                'availableBefore'  => (float) $availableBefore,
                'availableAfter'   => (float) ($availableBefore - $amount),
            ];
        });

        $this->checkAndNotifyLowBalance($result['user'], $result['availableBefore'], $result['availableAfter']);

        return $result['transaction'];
    }

    /**
     * Held → Available: Refund (dispute resolution বা cancellation)
     * ব্যবহার: Dispute refund, deal cancelled
     */
    public function release(
        User $user,
        float $amount,
        int $dealId,
        string $description = '',
        ?User $releasedBy = null
    ): WalletTransaction {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($user, $amount, $dealId, $description, $releasedBy) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($user->held_balance < $amount) {
                throw WalletException::insufficientHeld($amount, $user->held_balance);
            }

            $availableBefore = $user->available_balance;
            $heldBefore      = $user->held_balance;

            $user->decrement('held_balance', $amount);
            $user->increment('available_balance', $amount);

            return $this->log([
                'user_id'          => $user->id,
                'type'             => 'escrow_refund',
                'amount'           => $amount,
                'direction'        => 'credit',
                'balance_type'     => 'held_to_available',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore + $amount,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore - $amount,
                'reference_type'   => 'job_deals',
                'reference_id'     => $dealId,
                'description'      => $description ?: "Deal #{$dealId} — Escrow refund",
                'created_by_id'    => $releasedBy?->id,
            ]);
        });
    }

    /**
     * Held → 0: Worker এর held balance চূড়ান্তভাবে কাটো (milestone release)
     * ব্যবহার: Admin milestone release করলে worker এর held কাটে
     */
    public function deductHeld(
        User $user,
        float $amount,
        int $dealId,
        int $milestoneId,
        ?User $releasedBy = null
    ): WalletTransaction {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($user, $amount, $dealId, $milestoneId, $releasedBy) {
            $user = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            if ($user->held_balance < $amount) {
                throw WalletException::insufficientHeld($amount, $user->held_balance);
            }

            $availableBefore = $user->available_balance;
            $heldBefore      = $user->held_balance;

            $user->decrement('held_balance', $amount);

            return $this->log([
                'user_id'          => $user->id,
                'type'             => 'escrow_deduct_worker',
                'amount'           => $amount,
                'direction'        => 'debit',
                'balance_type'     => 'held',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore - $amount,
                'reference_type'   => 'job_deal_milestones',
                'reference_id'     => $milestoneId,
                'description'      => "Deal #{$dealId} — Milestone #{$milestoneId} worker deduct",
                'created_by_id'    => $releasedBy?->id,
            ]);
        });
    }

    /**
     * Agent কে milestone amount credit করো
     * ব্যবহার: Admin milestone release করলে agent পায়
     */
    public function creditAgent(
        User $agent,
        float $amount,
        int $dealId,
        int $milestoneId,
        ?User $releasedBy = null
    ): WalletTransaction {
        $this->validateAmount($amount);

        return DB::transaction(function () use ($agent, $amount, $dealId, $milestoneId, $releasedBy) {
            $agent = User::where('id', $agent->id)->lockForUpdate()->firstOrFail();

            $availableBefore = $agent->available_balance;
            $heldBefore      = $agent->held_balance;

            $agent->increment('available_balance', $amount);

            return $this->log([
                'user_id'          => $agent->id,
                'type'             => 'escrow_release_agent',
                'amount'           => $amount,
                'direction'        => 'credit',
                'balance_type'     => 'available',
                'available_before' => $availableBefore,
                'available_after'  => $availableBefore + $amount,
                'held_before'      => $heldBefore,
                'held_after'       => $heldBefore,
                'reference_type'   => 'job_deal_milestones',
                'reference_id'     => $milestoneId,
                'description'      => "Deal #{$dealId} — Milestone #{$milestoneId} agent payment",
                'created_by_id'    => $releasedBy?->id,
            ]);
        });
    }

    // ─────────────────────────────────────────────
    // SECTION 3 — Shortcut Methods (Semantic Wrappers)
    // ─────────────────────────────────────────────

    /** CV approval fee: 10 SAR কাটো */
    public function chargeCvApprovalFee(User $user, int $workerId): WalletTransaction
    {
        $fee = (float) setting('cv_approval_fee', 10);

        return $this->deduct(
            $user,
            $fee,
            'cv_approval_fee',
            "CV approval fee — Worker #{$workerId}",
            'workers',
            $workerId
        );
    }

    /** CV rejection refund: 10 SAR ফেরত */
    public function refundCvApprovalFee(User $user, int $workerId, User $admin): WalletTransaction
    {
        $fee = (float) setting('cv_approval_fee', 10);

        return $this->credit(
            $user,
            $fee,
            'cv_rejection_refund',
            "CV rejection refund — Worker #{$workerId}",
            'workers',
            $workerId,
            $admin
        );
    }

    /** Job fee reveal charge */
    public function chargeJobFeeReveal(User $user, int $jobPostId, float $cost): WalletTransaction
    {
        return $this->deduct(
            $user,
            $cost,
            'job_fee_reveal',
            "Job fee reveal — JobPost #{$jobPostId}",
            'job_posts',
            $jobPostId
        );
    }

    /** Contact reveal charge: 5 SAR */
    public function chargeContactReveal(User $user, int $workerId): WalletTransaction
    {
        $fee = (float) setting('contact_reveal_fee', 5);

        return $this->deduct(
            $user,
            $fee,
            'contact_reveal',
            "Contact reveal — Worker #{$workerId}",
            'workers',
            $workerId
        );
    }

    /** Referral bonus credit */
    public function creditReferralBonus(User $referrer, int $referralRewardId): WalletTransaction
    {
        $bonus = (float) setting('referral_bonus_sar', 20);

        return $this->credit(
            $referrer,
            $bonus,
            'referral_bonus',
            "Referral bonus — Reward #{$referralRewardId}",
            'referral_rewards',
            $referralRewardId
        );
    }

    /** Manual Admin adjustment */
    public function manualAdjustment(
        User $user,
        float $amount,
        string $direction,
        string $reason,
        User $admin
    ): WalletTransaction {
        if ($direction === 'credit') {
            return $this->credit($user, $amount, 'manual_adjustment', $reason, null, null, $admin);
        }

        return $this->deduct($user, $amount, 'manual_adjustment', $reason, null, null, $admin);
    }

    /** Withdrawal debit */
    public function chargeWithdrawal(User $user, float $amount, int $withdrawalId, User $admin): WalletTransaction
    {
        return $this->deduct(
            $user,
            $amount,
            'withdrawal_debit',
            "Withdrawal request #{$withdrawalId}",
            'withdrawal_requests',
            $withdrawalId,
            $admin
        );
    }

    /**
     * Wallet Recharge: Admin manually কোনো user এর available balance বাড়ায়
     * ব্যবহার: UserResource এর "Adjust Wallet" action (Admin recharge করলে)
     */
    public function rechargeWallet(
        User $user,
        float $amount,
        User $admin,
        ?string $note = null
    ): WalletTransaction {
        return $this->credit(
            $user,
            $amount,
            self::TYPE_WALLET_RECHARGE,
            $note ?: "Admin কর্তৃক Wallet Recharge — {$amount} SAR",
            null,
            null,
            $admin
        );
    }

    // ─────────────────────────────────────────────
    // SECTION 4 — Query Helpers
    // ─────────────────────────────────────────────

    /** User এর fresh balance নাও */
    public function getBalances(User $user): array
    {
        $fresh = User::find($user->id);

        return [
            'available' => (float) $fresh->available_balance,
            'held'      => (float) $fresh->held_balance,
            'total'     => (float) ($fresh->available_balance + $fresh->held_balance),
        ];
    }

    /** Balance check — throw না করে boolean */
    public function canAfford(User $user, float $amount): bool
    {
        return User::find($user->id)->available_balance >= $amount;
    }

    /** Withdrawal validation (ন্যূনতম amount + available balance check) */
    public function validateWithdrawal(User $user, float $amount): void
    {
        $minWithdrawal = (float) setting('min_withdrawal_sar', 50);

        if ($amount < $minWithdrawal) {
            throw WalletException::belowMinimumWithdrawal($minWithdrawal);
        }

        $fresh = User::find($user->id);
        if ($fresh->available_balance < $amount) {
            throw WalletException::insufficientBalance($amount, $fresh->available_balance);
        }
    }

    /**
     * User এর transaction history — Eloquent Builder রিটার্ন করে (execute করে না)
     * যাতে caller নিজের মতো filter/paginate করতে পারে।
     * ব্যবহার: Step 7.4 (MyWallet transaction table — Agent/Worker panel),
     *          Admin WalletTransactionResource
     */
    public function transactionsQuery(User $user, ?string $type = null): Builder
    {
        return WalletTransaction::query()
            ->where('user_id', $user->id)
            ->when($type, fn (Builder $q) => $q->where('type', $type))
            ->latest('created_at');
    }

    /**
     * User এর type অনুযায়ী total credit/debit summary (analytics widget-এর জন্য)
     * রিটার্ন: ['cv_approval_fee' => ['credit' => 0, 'debit' => 120.00], ...]
     */
    public function summaryByType(User $user): array
    {
        $rows = WalletTransaction::query()
            ->where('user_id', $user->id)
            ->selectRaw('type, direction, SUM(amount) as total')
            ->groupBy('type', 'direction')
            ->get();

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row->type][$row->direction] = (float) $row->total;
        }

        return $summary;
    }

    // ─────────────────────────────────────────────
    // SECTION 5 — Private Helpers
    // ─────────────────────────────────────────────

    private function validateAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw WalletException::negativeAmount();
        }
    }

    /**
     * wallet_low ইভেন্ট — শুধুমাত্র সেই মুহূর্তে fire হয় যখন available_balance
     * থ্রেশহোল্ডের (ডিফল্ট ৫০ SAR) *উপর থেকে নিচে* ক্রস করে। এতে balance
     * ইতিমধ্যে কম থাকা অবস্থায় প্রতিটা ছোট খরচে বারবার notification স্প্যাম
     * হওয়া এড়ানো যায় — একবারই সতর্ক করা হয়, ঠিক যখন balance প্রথমবার
     * থ্রেশহোল্ডের নিচে নামে।
     */
    private function checkAndNotifyLowBalance(User $user, float $availableBefore, float $availableAfter): void
    {
        $threshold = (float) setting('wallet_low_threshold', 50);

        if ($availableBefore >= $threshold && $availableAfter < $threshold) {
            $this->notifications->walletLow($user);
        }
    }

    private function log(array $data): WalletTransaction
    {
        $transaction = WalletTransaction::create($data);

        Log::channel('daily')->info('WalletTransaction', [
            'id'        => $transaction->id,
            'user_id'   => $data['user_id'],
            'type'      => $data['type'],
            'amount'    => $data['amount'],
            'direction' => $data['direction'],
        ]);

        return $transaction;
    }
}