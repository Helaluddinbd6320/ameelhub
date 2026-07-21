<?php

namespace App\Services;

use App\Models\User;
use App\Models\Worker;
use App\Models\JobPost;
use App\Models\JobDeal;
use App\Models\JobDealMilestone;
use App\Models\AgentNok;
use App\Models\WithdrawalRequest;
use App\Models\RechargeRequest;
use App\Models\AppNotification;
use App\Mail\CvStatusMail;
use App\Mail\JobStatusMail;
use App\Mail\NokMail;
use App\Mail\SelectionMail;
use App\Mail\EscrowMail;
use App\Mail\MilestoneMail;
use App\Mail\DisputeMail;
use App\Mail\DealCompletedMail;
use App\Mail\WithdrawalMail;
use App\Mail\RechargeMail;
use App\Mail\IqamaExpiryDigestMail;
use App\Mail\ReferralBonusMail;
use App\Mail\AgentVerificationMail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    // ─────────────────────────────────────────────
    // Core helpers
    // ─────────────────────────────────────────────

    protected function notify(User $user, string $type, string $title, string $body, array $data = []): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);
    }

    protected function sendMail(User $user, Mailable $mail): void
    {
        if ($user->email) {
            Mail::to($user->email)->queue($mail);
        }
    }

    /** super_admin + admin + staff — for DB bell */
    protected function adminUsersForDb(): Collection
    {
        return User::whereIn('role', ['super_admin', 'admin', 'staff'])->get();
    }

    /** super_admin + admin only — for email (avoid spamming data-entry staff) */
    protected function adminUsersForEmail(): Collection
    {
        return User::whereIn('role', ['super_admin', 'admin'])->get();
    }

    // ─────────────────────────────────────────────
    // CV events
    // ─────────────────────────────────────────────

    public function cvSubmitted(Worker $worker): void
    {
        $title = 'নতুন CV জমা পড়েছে';
        $body  = "{$worker->full_name_bn} একটি নতুন CV জমা দিয়েছেন, অনুমোদনের অপেক্ষায়।";
        $data  = ['worker_id' => $worker->id];

        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'cv_submitted', $title, $body, $data);
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new CvStatusMail($worker, 'submitted'));
        }
    }

    public function cvApproved(Worker $worker): void
    {
        $user = User::find($worker->worker_user_id);
        if (! $user) return;

        $this->notify($user, 'cv_approved', 'আপনার CV অনুমোদিত হয়েছে', 'অভিনন্দন! আপনার CV এখন পাবলিকভাবে দৃশ্যমান।', ['worker_id' => $worker->id]);
        $this->sendMail($user, new CvStatusMail($worker, 'approved'));
    }

    public function cvRejected(Worker $worker, string $reason): void
    {
        $user = User::find($worker->worker_user_id);
        if (! $user) return;

        $this->notify($user, 'cv_rejected', 'আপনার CV বাতিল হয়েছে', "কারণ: {$reason}", ['worker_id' => $worker->id, 'reason' => $reason]);
        $this->sendMail($user, new CvStatusMail($worker, 'rejected', $reason));
    }

    // ─────────────────────────────────────────────
    // Job Post events
    // ─────────────────────────────────────────────

    public function jobPosted(JobPost $job): void
    {
        $title = 'নতুন Job Post জমা পড়েছে';
        $body  = "{$job->job_title} — অনুমোদনের অপেক্ষায়।";
        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'job_posted', $title, $body, ['job_post_id' => $job->id]);
        }
    }

    public function jobApproved(JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify($agent, 'job_approved', 'আপনার Job Post অনুমোদিত হয়েছে', "{$job->job_title} এখন Active এবং পাবলিকভাবে দৃশ্যমান।", ['job_post_id' => $job->id]);
        $this->sendMail($agent, new JobStatusMail($job, 'approved'));
    }

    public function jobRejected(JobPost $job, string $reason): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify($agent, 'job_rejected', 'আপনার Job Post বাতিল হয়েছে', "কারণ: {$reason}", ['job_post_id' => $job->id, 'reason' => $reason]);
        $this->sendMail($agent, new JobStatusMail($job, 'rejected', $reason));
    }

    public function jobAutoClosed(JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify($agent, 'job_auto_closed', 'আপনার Job Post স্বয়ংক্রিয়ভাবে বন্ধ হয়েছে', "{$job->job_title} মেয়াদোত্তীর্ণ হওয়ায় বন্ধ করা হয়েছে।", ['job_post_id' => $job->id]);
        $this->sendMail($agent, new JobStatusMail($job, 'auto_closed'));
    }

    public function jobFilledAuto(JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify($agent, 'job_filled_auto', 'আপনার Job Post পূর্ণ হয়ে গেছে', "{$job->job_title} — সব vacancy পূরণ হয়েছে, স্বয়ংক্রিয়ভাবে বন্ধ করা হয়েছে।", ['job_post_id' => $job->id]);
    }

    public function interestReceived(JobPost $job, int $count = 1): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify(
            $agent,
            'interest_received',
            'নতুন Interest পাওয়া গেছে',
            "{$job->job_title} এর জন্য {$count} টি নতুন Interest জমা পড়েছে।",
            ['job_post_id' => $job->id, 'count' => $count]
        );
    }

    /**
     * EXTENSION (not in original Section 16 list, but referenced by
     * JobLifecycleService::notifyOnClose TODO) — notifies a single
     * interested worker/agent that a job they applied to has closed.
     */
    public function jobClosedForInterestedParty(JobPost $job, User $user): void
    {
        $this->notify(
            $user,
            'job_closed_notice',
            'আপনার আবেদন করা Job বন্ধ হয়েছে',
            "{$job->job_title} — এই Job Post টি এখন বন্ধ হয়ে গেছে।",
            ['job_post_id' => $job->id]
        );
    }

    /**
     * EXTENSION (referenced by Step 9.3 "stale job alerts" and
     * JobLifecycleService::alertStale) — not itemized individually in
     * Section 16 but implied by the Phase 9.3 scope.
     */
    public function jobStaleAlert(JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify(
            $agent,
            'job_stale_alert',
            'আপনার Job Post নিষ্ক্রিয় পড়ে আছে',
            "{$job->job_title} — অনেকদিন ধরে কোনো Interest পায়নি। পোস্টটি পর্যালোচনা করুন।",
            ['job_post_id' => $job->id]
        );
    }

    // ─────────────────────────────────────────────
    // Nok events
    // ─────────────────────────────────────────────

    public function nokSent(AgentNok $nok): void
    {
        $worker = User::find($nok->worker_user_id);
        if (! $worker) return;

        $this->notify($worker, 'nok_sent', 'নতুন Job Offer (Nok) এসেছে', 'একজন এজেন্ট আপনাকে একটি জব অফার পাঠিয়েছেন।', ['nok_id' => $nok->id, 'job_post_id' => $nok->job_post_id]);
        $this->sendMail($worker, new NokMail($nok, 'sent'));
    }

    public function nokAccepted(AgentNok $nok): void
    {
        $agent = User::find($nok->agent_id);
        if (! $agent) return;

        $this->notify($agent, 'nok_accepted', 'আপনার Nok গ্রহণ করা হয়েছে', 'Worker আপনার job offer গ্রহণ করেছেন।', ['nok_id' => $nok->id]);
        $this->sendMail($agent, new NokMail($nok, 'accepted'));
    }

    public function nokRejected(AgentNok $nok): void
    {
        $agent = User::find($nok->agent_id);
        if (! $agent) return;

        $this->notify($agent, 'nok_rejected', 'আপনার Nok প্রত্যাখ্যান করা হয়েছে', 'Worker আপনার job offer প্রত্যাখ্যান করেছেন।', ['nok_id' => $nok->id]);
    }

    public function nokExpired(AgentNok $nok): void
    {
        $agent  = User::find($nok->agent_id);
        $worker = User::find($nok->worker_user_id);

        if ($agent) {
            $this->notify($agent, 'nok_expired', 'Nok মেয়াদোত্তীর্ণ হয়েছে', '৪৮ ঘণ্টার মধ্যে Worker সাড়া দেননি।', ['nok_id' => $nok->id]);
        }
        if ($worker) {
            $this->notify($worker, 'nok_expired', 'Job Offer মেয়াদোত্তীর্ণ হয়েছে', 'আপনি ৪৮ ঘণ্টার মধ্যে সাড়া দেননি, offer মেয়াদোত্তীর্ণ হয়ে গেছে।', ['nok_id' => $nok->id]);
        }
    }

    // ─────────────────────────────────────────────
    // Selection events
    // ─────────────────────────────────────────────

    public function workerSelected(Worker $worker, JobPost $job): void
    {
        $user = User::find($worker->worker_user_id);
        if (! $user) return;

        $this->notify($user, 'worker_selected', 'আপনি Selected হয়েছেন', "{$job->job_title} এর জন্য আপনাকে Select করা হয়েছে। গ্রহণ/প্রত্যাখ্যান করুন।", ['worker_id' => $worker->id, 'job_post_id' => $job->id]);
        $this->sendMail($user, new SelectionMail($worker, $job, 'selected'));
    }

    public function workerAccepted(Worker $worker, JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        $recipients = $this->adminUsersForDb();
        if ($agent) $recipients->push($agent);

        foreach ($recipients as $r) {
            $this->notify($r, 'worker_accepted', 'Worker Selection গ্রহণ করেছেন', "{$worker->full_name_bn} — {$job->job_title} এর জন্য গ্রহণ করেছেন।", ['worker_id' => $worker->id, 'job_post_id' => $job->id]);
        }
        if ($agent) {
            $this->sendMail($agent, new SelectionMail($worker, $job, 'accepted'));
        }
    }

    public function workerRejectedSelection(Worker $worker, JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        if (! $agent) return;

        $this->notify($agent, 'worker_rejected_selection', 'Worker Selection প্রত্যাখ্যান করেছেন', "{$worker->full_name_bn} — {$job->job_title} এর selection প্রত্যাখ্যান করেছেন।", ['worker_id' => $worker->id, 'job_post_id' => $job->id]);
    }

    public function selectionExpired(Worker $worker, JobPost $job): void
    {
        $agent = User::find($job->posted_by_id);
        $user  = User::find($worker->worker_user_id);

        if ($agent) {
            $this->notify($agent, 'selection_expired', 'Selection মেয়াদোত্তীর্ণ হয়েছে', "{$worker->full_name_bn} এর selection মেয়াদোত্তীর্ণ হয়ে গেছে।", ['worker_id' => $worker->id, 'job_post_id' => $job->id]);
        }
        if ($user) {
            $this->notify($user, 'selection_expired', 'Selection মেয়াদোত্তীর্ণ হয়েছে', "{$job->job_title} এর জন্য আপনার selection মেয়াদোত্তীর্ণ হয়ে গেছে।", ['job_post_id' => $job->id]);
        }
    }

    // ─────────────────────────────────────────────
    // Escrow / Milestone events
    //
    // NOTE: JobDealMilestone's relation to its parent deal is `deal()`
    // (confirmed from MilestoneService/DisputeService usage), NOT
    // `jobDeal()`. All methods below use `$m->deal` accordingly.
    // ─────────────────────────────────────────────

    public function escrowHoldConfirmed(JobDeal $deal): void
    {
        $deal->loadMissing(['worker', 'agent']);

        $worker = User::find($deal->worker->worker_user_id ?? null);
        $agent  = User::find($deal->agent_id);

        if ($worker) {
            $this->notify($worker, 'escrow_hold_confirmed', 'Escrow Hold নিশ্চিত হয়েছে', 'আপনার Deal এর টাকা নিরাপদে Escrow তে রাখা হয়েছে।', ['deal_id' => $deal->id]);
            $this->sendMail($worker, new EscrowMail($deal));
        }
        if ($agent) {
            $this->notify($agent, 'escrow_hold_confirmed', 'Escrow Hold নিশ্চিত হয়েছে', 'Deal এর টাকা Escrow তে হোল্ড করা হয়েছে।', ['deal_id' => $deal->id]);
            $this->sendMail($agent, new EscrowMail($deal));
        }
    }

    public function milestoneWorkerConfirmed(JobDealMilestone $m): void
    {
        $m->loadMissing('deal');
        $agent = User::find($m->deal->agent_id);
        $recipients = $this->adminUsersForDb();
        if ($agent) $recipients->push($agent);

        foreach ($recipients as $r) {
            $this->notify($r, 'milestone_worker_confirmed', 'Worker Milestone Confirm করেছেন', "Milestone #{$m->milestone_number}: {$m->title} — Worker confirm করেছেন।", ['milestone_id' => $m->id]);
        }
        if ($agent) $this->sendMail($agent, new MilestoneMail($m, 'worker_confirmed'));
    }

    public function milestoneAgentConfirmed(JobDealMilestone $m): void
    {
        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'milestone_agent_confirmed', 'Agent Milestone Confirm করেছেন', "Milestone #{$m->milestone_number}: {$m->title} — Agent confirm করেছেন, Release এর অপেক্ষায়।", ['milestone_id' => $m->id]);
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new MilestoneMail($m, 'agent_confirmed'));
        }
    }

    public function milestoneReleased(JobDealMilestone $m, ?string $pdfPath = null): void
    {
        $m->loadMissing('deal.worker');
        $deal   = $m->deal;
        $agent  = User::find($deal->agent_id);
        $worker = User::find($deal->worker->worker_user_id ?? null);

        if ($agent) {
            $this->notify($agent, 'milestone_released', 'Milestone Release হয়েছে', "Milestone #{$m->milestone_number} — {$m->agent_receives_sar} SAR আপনার Wallet এ জমা হয়েছে।", ['milestone_id' => $m->id]);
            $this->sendMail($agent, new MilestoneMail($m, 'released', $pdfPath));
        }
        if ($worker) {
            $this->notify($worker, 'milestone_released', 'Milestone Release হয়েছে', "Milestone #{$m->milestone_number}: {$m->title} সম্পন্ন হয়েছে।", ['milestone_id' => $m->id]);
            $this->sendMail($worker, new MilestoneMail($m, 'released', $pdfPath));
        }
    }

    // ─────────────────────────────────────────────
    // Dispute events
    // ─────────────────────────────────────────────

    public function disputeRaised(JobDealMilestone $m): void
    {
        $title = '⚠ জরুরি: Dispute উত্থাপিত হয়েছে';
        $body  = "Deal #{$m->job_deal_id}, Milestone #{$m->milestone_number} এ dispute তৈরি হয়েছে। এখনই review করুন।";

        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'dispute_raised', $title, $body, ['milestone_id' => $m->id, 'deal_id' => $m->job_deal_id]);
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new DisputeMail($m, 'raised'));
        }
    }

    public function disputeResolved(JobDealMilestone $m): void
    {
        $m->loadMissing('deal.worker');
        $deal   = $m->deal;
        $agent  = User::find($deal->agent_id);
        $worker = User::find($deal->worker->worker_user_id ?? null);

        foreach (array_filter([$agent, $worker]) as $u) {
            $this->notify($u, 'dispute_resolved', 'Dispute সমাধান হয়েছে', "Milestone #{$m->milestone_number} এর dispute সমাধান করা হয়েছে: {$m->resolution}", ['milestone_id' => $m->id]);
            $this->sendMail($u, new DisputeMail($m, 'resolved'));
        }
    }

    // ─────────────────────────────────────────────
    // Deal completion
    // ─────────────────────────────────────────────

    public function dealCompleted(JobDeal $deal, ?string $pdfPath = null): void
    {
        $deal->loadMissing(['worker', 'agent']);
        $agent  = User::find($deal->agent_id);
        $worker = User::find($deal->worker->worker_user_id ?? null);

        foreach (array_filter([$agent, $worker]) as $u) {
            $this->notify($u, 'deal_completed', 'Deal সম্পন্ন হয়েছে ✅', "Deal #{$deal->uuid} সফলভাবে সম্পন্ন হয়েছে।", ['deal_id' => $deal->id]);
            $this->sendMail($u, new DealCompletedMail($deal, $pdfPath));
        }
    }

    // ─────────────────────────────────────────────
    // Withdrawal events
    // ─────────────────────────────────────────────

    public function withdrawalRequested(WithdrawalRequest $w): void
    {
        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'withdrawal_requested', 'নতুন Withdrawal Request', "{$w->amount} SAR এর Withdrawal Request জমা পড়েছে।", ['withdrawal_id' => $w->id]);
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new WithdrawalMail($w, 'requested'));
        }
    }

    public function withdrawalApproved(WithdrawalRequest $w): void
    {
        $user = User::find($w->user_id);
        if (! $user) return;

        $this->notify($user, 'withdrawal_approved', 'Withdrawal অনুমোদিত হয়েছে', "{$w->amount} SAR এর Withdrawal অনুমোদিত হয়েছে।", ['withdrawal_id' => $w->id]);
        $this->sendMail($user, new WithdrawalMail($w, 'approved'));
    }

    public function withdrawalRejected(WithdrawalRequest $w, string $reason): void
    {
        $user = User::find($w->user_id);
        if (! $user) return;

        $this->notify($user, 'withdrawal_rejected', 'Withdrawal বাতিল হয়েছে', "কারণ: {$reason}", ['withdrawal_id' => $w->id, 'reason' => $reason]);
        $this->sendMail($user, new WithdrawalMail($w, 'rejected', $reason));
    }

    // ─────────────────────────────────────────────
    // Recharge events
    //
    // EXTENSION (added post-launch — worker/agent wallet top-up flow,
    // same shape as the Withdrawal events section above; RechargeRequest
    // model uses 'user_id' as the FK column, same as WithdrawalRequest).
    // ─────────────────────────────────────────────

    public function rechargeRequested(RechargeRequest $r): void
    {
        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify($admin, 'recharge_requested', 'নতুন Recharge Request', "{$r->amount} SAR এর Recharge Request জমা পড়েছে ({$r->payment_method}), যাচাই করে অনুমোদন দিন।", ['recharge_id' => $r->id]);
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new RechargeMail($r, 'requested'));
        }
    }

    public function rechargeApproved(RechargeRequest $r): void
    {
        $user = User::find($r->user_id);
        if (! $user) return;

        $this->notify($user, 'recharge_approved', 'Recharge অনুমোদিত হয়েছে', "{$r->amount} SAR আপনার Wallet এ যোগ হয়েছে।", ['recharge_id' => $r->id]);
        $this->sendMail($user, new RechargeMail($r, 'approved'));
    }

    public function rechargeRejected(RechargeRequest $r, string $reason): void
    {
        $user = User::find($r->user_id);
        if (! $user) return;

        $this->notify($user, 'recharge_rejected', 'Recharge Request বাতিল হয়েছে', "কারণ: {$reason}", ['recharge_id' => $r->id, 'reason' => $reason]);
        $this->sendMail($user, new RechargeMail($r, 'rejected', $reason));
    }

    // ─────────────────────────────────────────────
    // Wallet
    // ─────────────────────────────────────────────

    public function walletLow(User $user): void
    {
        $this->notify($user, 'wallet_low', 'Wallet Balance কম', 'আপনার Wallet balance ৫০ SAR এর নিচে নেমে গেছে। রিচার্জ করুন।', ['balance' => (float) $user->available_balance]);
    }

    // ─────────────────────────────────────────────
    // Iqama expiry (daily digest — used by Step 9.3 scheduler)
    // ─────────────────────────────────────────────

    public function iqamaExpiryDigest(Collection $expiringWorkers): void
    {
        if ($expiringWorkers->isEmpty()) return;

        foreach ($this->adminUsersForDb() as $admin) {
            $this->notify(
                $admin,
                'iqama_expiry_30d',
                'Iqama মেয়াদ শেষের কাছাকাছি',
                "{$expiringWorkers->count()} জন Worker এর Iqama আগামী ৩০ দিনের মধ্যে মেয়াদোত্তীর্ণ হবে।",
                ['worker_ids' => $expiringWorkers->pluck('id')->all()]
            );
        }
        foreach ($this->adminUsersForEmail() as $admin) {
            $this->sendMail($admin, new IqamaExpiryDigestMail($expiringWorkers));
        }
    }

    // ─────────────────────────────────────────────
    // Referral
    // ─────────────────────────────────────────────

    public function referralBonusPaid(User $referrer, float $amount): void
    {
        $this->notify($referrer, 'referral_bonus_paid', 'Referral Bonus পেয়েছেন', "আপনি {$amount} SAR referral bonus পেয়েছেন।", ['amount' => $amount]);
        $this->sendMail($referrer, new ReferralBonusMail($referrer, $amount));
    }

    // ─────────────────────────────────────────────
    // Agent verification
    // ─────────────────────────────────────────────

    public function agentVerified(User $agent): void
    {
        $this->notify($agent, 'agent_verified', 'আপনার Agent Account Verified', 'অভিনন্দন! আপনি এখন Job Post করতে পারবেন।', []);
        $this->sendMail($agent, new AgentVerificationMail($agent, 'verified'));
    }

    public function agentRejected(User $agent, string $reason): void
    {
        $this->notify($agent, 'agent_rejected', 'আপনার Agent Verification বাতিল হয়েছে', "কারণ: {$reason}", ['reason' => $reason]);
        $this->sendMail($agent, new AgentVerificationMail($agent, 'rejected', $reason));
    }
}