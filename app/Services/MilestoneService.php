<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\JobDeal;
use App\Models\JobDealMilestone;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MilestoneService
{
    public function __construct(
        protected WalletService $walletService,
        protected ReferralService $referralService,
        protected NotificationService $notifications
    ) {
    }

    /**
     * Deal Confirm হওয়ার সাথে সাথেই ৩টি Milestone তৈরি করে —
     * settings থেকে percentage ও title (বাংলা + English) টেনে নিয়ে,
     * প্রতিটির amount/commission/agent_receives হিসাব করে।
     */
    public function createForDeal(JobDeal $deal): void
    {
        DB::transaction(function () use ($deal) {

            if ($deal->milestones()->exists()) {
                return;
            }

            $totalFee      = (float) $deal->agent_fee_sar;
            $commissionPct = (float) $deal->chapai_commission_pct;

            $config = [
                1 => [
                    'pct'      => (float) Setting::get('milestone_1_pct', 20),
                    'title_bn' => Setting::get('milestone_1_title', 'Worker সৌদি আরবে পৌঁছেছে'),
                    'title_en' => Setting::get('milestone_1_title_en', 'Worker arrived in Saudi Arabia'),
                ],
                2 => [
                    'pct'      => (float) Setting::get('milestone_2_pct', 40),
                    'title_bn' => Setting::get('milestone_2_title', 'Company Iqama/Kafala সম্পন্ন'),
                    'title_en' => Setting::get('milestone_2_title_en', 'Iqama/Kafala completed'),
                ],
                3 => [
                    'pct'      => (float) Setting::get('milestone_3_pct', 40),
                    'title_bn' => Setting::get('milestone_3_title', '১ মাস কাজ সম্পন্ন, সব ঠিকঠাক'),
                    'title_en' => Setting::get('milestone_3_title_en', '1 month work completed successfully'),
                ],
            ];

            foreach ($config as $number => $milestone) {
                $amounts = $this->calculateAmounts($totalFee, $milestone['pct'], $commissionPct);

                // SECURITY FIX (Step 10.7 audit): 'status' is now guarded
                // on the JobDealMilestone model (it must only ever be
                // changed via forceFill() inside this service /
                // DisputeService — never by a plain update()/create()).
                // A plain create() here would silently drop 'status',
                // leaving every new milestone with a NULL status instead
                // of 'pending'. forceCreate() bypasses mass-assignment
                // protection for this trusted, system-controlled insert.
                JobDealMilestone::forceCreate([
                    'job_deal_id'        => $deal->id,
                    'milestone_number'   => $number,
                    'title'              => $milestone['title_bn'],
                    'description'        => $milestone['title_en'],
                    'percentage'         => $milestone['pct'],
                    'amount_sar'         => $amounts['amount_sar'],
                    'commission_sar'     => $amounts['commission_sar'],
                    'agent_receives_sar' => $amounts['agent_receives_sar'],
                    'status'             => 'pending',
                ]);
            }
        });
    }

    public function calculateAmounts(float $totalFee, float $percentage, float $commissionPct): array
    {
        $amountSar     = round($totalFee * $percentage / 100, 2);
        $commissionSar = round($amountSar * $commissionPct / 100, 2);
        $agentReceives = round($amountSar - $commissionSar, 2);

        return [
            'amount_sar'         => $amountSar,
            'commission_sar'     => $commissionSar,
            'agent_receives_sar' => $agentReceives,
        ];
    }

    /**
     * Worker কর্তৃক Milestone কনফার্ম (যেমন: সৌদি পৌঁছানো / Iqama সম্পন্ন / ১ মাস কাজ সম্পন্ন)।
     * শুধুমাত্র deal-এর নিজস্ব worker_user_id ধারী ইউজার কনফার্ম করতে পারবে।
     * ধারাবাহিকতা নিয়ম: আগের milestone admin_released না হলে পরেরটা কনফার্ম করা যাবে না।
     */
    public function confirmByWorker(JobDealMilestone $milestone, User $user): JobDealMilestone
    {
        $milestone = DB::transaction(function () use ($milestone, $user) {
            [$milestone, $deal] = $this->lockMilestoneAndDeal($milestone->id);

            if (! $deal->worker || $deal->worker->worker_user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'milestone' => 'আপনি এই মাইলস্টোন কনফার্ম করার অনুমতিপ্রাপ্ত নন।',
                ]);
            }

            $this->guardNotDisputed($deal, $milestone);

            if ($milestone->status !== 'pending') {
                throw ValidationException::withMessages([
                    'milestone' => 'এই মাইলস্টোন ইতিমধ্যে কনফার্ম করা হয়েছে অথবা এখন কনফার্ম করার ধাপে নেই।',
                ]);
            }

            $this->guardSequentialOrder($deal, $milestone);

            $milestone->forceFill([
                'status'              => 'worker_confirmed',
                'worker_confirmed_at' => now(),
            ])->save();

            return $milestone->fresh();
        });

        $this->notifications->milestoneWorkerConfirmed($milestone);

        return $milestone;
    }

    /**
     * Agent কর্তৃক Milestone কনফার্ম — শুধুমাত্র Worker কনফার্ম করার পরেই সম্ভব।
     */
    public function confirmByAgent(JobDealMilestone $milestone, User $user): JobDealMilestone
    {
        $milestone = DB::transaction(function () use ($milestone, $user) {
            [$milestone, $deal] = $this->lockMilestoneAndDeal($milestone->id);

            if ($deal->agent_id !== $user->id) {
                throw ValidationException::withMessages([
                    'milestone' => 'আপনি এই মাইলস্টোন কনফার্ম করার অনুমতিপ্রাপ্ত নন।',
                ]);
            }

            $this->guardNotDisputed($deal, $milestone);

            if ($milestone->status !== 'worker_confirmed') {
                throw ValidationException::withMessages([
                    'milestone' => 'Worker এখনো এই মাইলস্টোন কনফার্ম করেননি।',
                ]);
            }

            $milestone->forceFill([
                'status'             => 'agent_confirmed',
                'agent_confirmed_at' => now(),
            ])->save();

            return $milestone->fresh();
        });

        $this->notifications->milestoneAgentConfirmed($milestone);

        return $milestone;
    }

    /**
     * Admin কর্তৃক Milestone Release — Worker + Agent উভয়ে কনফার্ম করার পরেই সম্ভব।
     * এখানেই আসল money movement হয়:
     *   - Worker এর held balance থেকে চূড়ান্ত deduct (escrow_deduct_worker)
     *   - Agent কে তার অংশ credit (escrow_release_agent)
     * শেষ (৩য়) মাইলস্টোন হলে পুরো Deal সম্পন্ন (completed) হিসেবে মার্ক হবে,
     * এবং এই মুহূর্তেই referral bonus চেক/pay করা হয়, ও deal_completed
     * notification পাঠানো হয়।
     * Release সফল হলে PDF Receipt তৈরি হয়ে private disk-এ সংরক্ষিত হয়
     * এবং সেই receipt পাথ milestone_released (+ deal_completed) মেইলে সংযুক্ত হয়।
     */
    public function releaseByAdmin(JobDealMilestone $milestone, User $admin): JobDealMilestone
    {
        $milestone = DB::transaction(function () use ($milestone, $admin) {
            [$milestone, $deal] = $this->lockMilestoneAndDeal($milestone->id);

            $this->guardNotDisputed($deal, $milestone);

            if ($milestone->status !== 'agent_confirmed') {
                throw ValidationException::withMessages([
                    'milestone' => 'Agent এখনো কনফার্ম করেননি, তাই এই মাইলস্টোন release করা যাবে না।',
                ]);
            }

            $workerUser = User::find($deal->worker->worker_user_id);

            if (! $workerUser) {
                throw ValidationException::withMessages([
                    'milestone' => 'Worker এর ইউজার অ্যাকাউন্ট খুঁজে পাওয়া যায়নি।',
                ]);
            }

            try {
                $this->walletService->deductHeld(
                    $workerUser,
                    (float) $milestone->amount_sar,
                    $deal->id,
                    $milestone->id,
                    $admin
                );

                $this->walletService->creditAgent(
                    $deal->agent,
                    (float) $milestone->agent_receives_sar,
                    $deal->id,
                    $milestone->id,
                    $admin
                );
            } catch (WalletException $e) {
                throw ValidationException::withMessages([
                    'milestone' => $e->getMessage(),
                ]);
            }

            $milestone->forceFill([
                'status'            => 'admin_released',
                'admin_released_at' => now(),
                'released_by_id'    => $admin->id,
            ])->save();

            if ($milestone->milestone_number === 1 && $deal->status === 'confirmed') {
                $deal->forceFill([
                    'status'     => 'working',
                    'working_at' => now(),
                ])->save();
            }

            if ($milestone->milestone_number === 3) {
                $deal->forceFill([
                    'status'       => 'completed',
                    'completed_at' => now(),
                ])->save();

                // Deal সম্পন্ন — referral bonus চেক (worker + agent উভয়ের জন্য)।
                // ReferralService নিজস্ব DB::transaction() ব্যবহার করে (Laravel
                // savepoint দিয়ে nested-safe), তাই বাইরের transaction এর
                // ভেতরে কল করলেও কোনো সমস্যা নেই।
                $this->referralService->checkAndPayBonusForDeal($deal);
            }

            return $milestone->fresh();
        });

        // PDF Receipt generation — DB transaction এর বাইরে (file I/O কখনো lock ধরে রাখা উচিত না)
        $receiptPath = $this->generateReceipt($milestone);

        $this->notifications->milestoneReleased($milestone, $receiptPath);

        if ($milestone->milestone_number === 3) {
            $deal = $milestone->deal()->firstOrFail();
            $this->notifications->dealCompleted($deal, $receiptPath);
        }

        // NOTE: escrow_hold_confirmed (Section 16) fires when the worker
        // first accepts a selection and WalletService::hold() runs — that
        // happens in the Job Selection flow (Step 6.2 / JobSelectionService),
        // not here. Wire NotificationService::escrowHoldConfirmed() there
        // once that service file is shared.

        return $milestone->fresh();
    }

    /**
     * Milestone Release হওয়ার পর PDF Receipt তৈরি করে private disk-এ সংরক্ষণ করে।
     * ব্যর্থ হলেও money movement আগেই সম্পন্ন হয়ে গেছে বলে exception ছোঁড়া হয় না — শুধু log করা হয়।
     */
    public function generateReceipt(JobDealMilestone $milestone): ?string
    {
        try {
            $milestone->loadMissing(['deal.worker', 'deal.agent', 'deal.jobPost', 'releasedBy']);
            $deal = $milestone->deal;

            $pdf = Pdf::loadView('pdfs.milestone-receipt', [
                'milestone' => $milestone,
                'deal'      => $deal,
            ]);

            $filename = 'milestone-receipts/' . Str::ulid() . '.pdf';

            Storage::disk('private_docs')->put($filename, $pdf->output());

            $milestone->forceFill(['receipt_path' => $filename])->save();

            return $filename;
        } catch (\Throwable $e) {
            Log::error('Milestone receipt generation failed: ' . $e->getMessage(), [
                'milestone_id' => $milestone->id,
            ]);

            return null;
        }
    }

    // ─── Private helpers ─────────────────────────────────────────────

    private function lockMilestoneAndDeal(int $milestoneId): array
    {
        $milestone = JobDealMilestone::where('id', $milestoneId)->lockForUpdate()->firstOrFail();
        $deal = JobDeal::where('id', $milestone->job_deal_id)->lockForUpdate()->firstOrFail();

        return [$milestone, $deal];
    }

    private function guardNotDisputed(JobDeal $deal, JobDealMilestone $milestone): void
    {
        if ($deal->isDisputed() || $milestone->isDisputed()) {
            throw ValidationException::withMessages([
                'milestone' => 'এই ডিলে একটি বিরোধ (dispute) চলমান আছে, তাই এখন এই কাজ করা যাবে না।',
            ]);
        }
    }

    private function guardSequentialOrder(JobDeal $deal, JobDealMilestone $milestone): void
    {
        if ($milestone->milestone_number <= 1) {
            return;
        }

        $previous = JobDealMilestone::where('job_deal_id', $deal->id)
            ->where('milestone_number', $milestone->milestone_number - 1)
            ->first();

        if (! $previous || $previous->status !== 'admin_released') {
            throw ValidationException::withMessages([
                'milestone' => 'আগের মাইলস্টোন সম্পন্ন (release) না হওয়া পর্যন্ত এটি কনফার্ম করা যাবে না।',
            ]);
        }
    }
}