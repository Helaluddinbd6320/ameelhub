<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\JobDeal;
use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\JobSelection;
use App\Models\Setting;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobSelectionService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    /**
     * Agent একটি job_interest থেকে Worker কে চূড়ান্ত Select করে —
     * job_selections এ রেকর্ড তৈরি করে এবং Worker কে notify করে
     * (worker_selected — DB + Email)।
     */
    public function select(int $jobInterestId, User $agent): JobSelection
    {
        [$selection, $jobPost, $workerModel] = DB::transaction(function () use ($jobInterestId, $agent) {

            $interest = JobInterest::lockForUpdate()->findOrFail($jobInterestId);
            $jobPost  = JobPost::lockForUpdate()->findOrFail($interest->job_post_id);

            // BUG FIX (Helal-reported, Step 10.9 audit): strict `!==`
            // comparison is a classic PHP/PDO gotcha — MySQL values can
            // come back through PDO as strings while $agent->id is an int,
            // making "5" !== 5 evaluate to TRUE (they're actually equal)
            // and falsely rejecting the real owner. Same bug class already
            // fixed in JobInterests.php / BrowseWorkers.php (Agent panel
            // ownership checks). Casting both sides to int fixes it.
            if ((int) $jobPost->posted_by_id !== (int) $agent->id) {
                throw ValidationException::withMessages([
                    'job_interest_id' => 'আপনি শুধুমাত্র নিজের পোস্ট করা Job থেকে Worker Select করতে পারবেন।',
                ]);
            }

            if ($interest->status !== 'pending') {
                $reason = match ($interest->status) {
                    'selected' => 'এই Worker কে ইতিমধ্যে Select করা হয়েছে।',
                    'rejected' => 'এই আবেদন ইতিমধ্যে প্রত্যাখ্যাত হয়েছে, Select করা যাবে না।',
                    'hired'    => 'এই Worker ইতিমধ্যে Hire হয়ে গেছে।',
                    default    => 'এই আবেদনের উপর ইতিমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
                };

                throw ValidationException::withMessages(['job_interest_id' => $reason]);
            }

            if (in_array($jobPost->status, ['closed', 'filled', 'paused'], true)) {
                throw ValidationException::withMessages([
                    'job_interest_id' => 'এই Job বর্তমানে Select করার জন্য উপযুক্ত অবস্থায় নেই।',
                ]);
            }

            if ($jobPost->filled_count >= $jobPost->vacancies) {
                throw ValidationException::withMessages([
                    'job_interest_id' => 'এই Job এর সব পদ ইতিমধ্যে পূরণ হয়ে গেছে।',
                ]);
            }

            $alreadyPendingSelection = JobSelection::where('job_interest_id', $interest->id)
                ->where('worker_response', 'pending')
                ->exists();

            if ($alreadyPendingSelection) {
                throw ValidationException::withMessages([
                    'job_interest_id' => 'এই আবেদনের জন্য ইতিমধ্যে একটি Selection পাঠানো আছে, Worker এর উত্তরের অপেক্ষায়।',
                ]);
            }

            $expireHours = (int) Setting::get('selection_expire_hours', 48);

            $selection = JobSelection::create([
                'job_post_id'          => $jobPost->id,
                'job_interest_id'      => $interest->id,
                'worker_id'            => $interest->worker_id,
                'agent_id'             => $agent->id,
                'agent_fee_sar'        => $jobPost->agent_fee_sar,
                'notification_sent_at' => now(),
                'worker_response'      => 'pending',
                'expires_at'           => now()->addHours($expireHours),
            ]);

            // status guarded ফিল্ড — forceFill বাধ্যতামূলক
            $interest->forceFill(['status' => 'selected'])->save();

            return [$selection, $jobPost, Worker::find($interest->worker_id)];
        });

        // DB commit হওয়ার পরে notify — rollback হলে ভুল notification এড়াতে।
        // $workerModel null হলে (agent_created placeholder, claim হয়নি) —
        // NotificationService::workerSelected() নিজেই এই কেস গার্ড করে।
        if ($workerModel) {
            $this->notifications->workerSelected($workerModel, $jobPost);
        }

        return $selection;
    }

    /**
     * Worker একটি pending Selection গ্রহণ করলে —
     * 1. Escrow hold (Worker এর available_balance থেকে agent_fee_sar পরিমাণ held এ যায়)
     * 2. job_deals এ রেকর্ড তৈরি (commission calculate করে)
     * 3. Milestone তৈরি (MilestoneService::createForDeal — Step 6.3)
     * 4. job_interests status = hired
     * 5. job_posts filled_count বৃদ্ধি + প্রয়োজনে status = filled
     * 6. Escrow hold + Worker Accept — উভয় ইভেন্ট notify (escrow_hold_confirmed, worker_accepted)
     */
    public function accept(int $selectionId, User $worker): JobDeal
    {
        $result = DB::transaction(function () use ($selectionId, $worker) {

            $selection   = JobSelection::lockForUpdate()->findOrFail($selectionId);
            $workerModel = Worker::where('worker_user_id', $worker->id)->firstOrFail();

            // BUG FIX (Helal-reported, Step 10.9 audit): same PDO
            // string/int strict-comparison bug as select() above.
            if ((int) $selection->worker_id !== (int) $workerModel->id) {
                throw ValidationException::withMessages([
                    'job_selection_id' => 'এই Selection আপনার জন্য নয়।',
                ]);
            }

            if ($selection->worker_response !== 'pending') {
                throw ValidationException::withMessages([
                    'job_selection_id' => 'এই Selection এর উপর ইতিমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
                ]);
            }

            if ($selection->expires_at->isPast()) {
                $selection->fill(['worker_response' => 'expired'])->save();

                throw ValidationException::withMessages([
                    'job_selection_id' => 'এই Selection এর মেয়াদ শেষ হয়ে গেছে।',
                ]);
            }

            $jobPost   = JobPost::lockForUpdate()->findOrFail($selection->job_post_id);
            $agentFee  = (float) $selection->agent_fee_sar;

            if (! app(WalletService::class)->canAfford($worker, $agentFee)) {
                throw ValidationException::withMessages([
                    'job_selection_id' => "Wallet এ পর্যাপ্ত ব্যালেন্স নেই। প্রয়োজন: {$agentFee} SAR। রিচার্জ করে আবার চেষ্টা করুন।",
                ]);
            }

            // ── Commission আগে calculate করে নিন (DB তে guarded কলামগুলো NOT NULL,
            //    কোনো default value নেই — তাই create()->forceFill() দুই-ধাপে করলে
            //    প্রথম ধাপেই SQL error হবে। forceCreate() দিয়ে সব ফিল্ড এক ধাপে insert করুন) ──
            $commissionPct = (float) Setting::get('deal_commission_pct', 8);
            $commissionSar = round($agentFee * $commissionPct / 100, 2);
            $agentReceives = round($agentFee - $commissionSar, 2);

            $deal = JobDeal::forceCreate([
                'uuid'                  => (string) str()->uuid(),
                'job_selection_id'      => $selection->id,
                'job_post_id'           => $jobPost->id,
                'worker_id'             => $selection->worker_id,
                'agent_id'              => $selection->agent_id,
                'agent_fee_sar'         => $agentFee,
                'chapai_commission_pct' => $commissionPct,
                'chapai_commission_sar' => $commissionSar,
                'agent_receives_sar'    => $agentReceives,
                'status'                => 'confirmed',
                'confirmed_at'          => now(),
            ]);

            // ── Escrow Hold: Worker এর available → held ──
            try {
                app(WalletService::class)->hold(
                    $worker,
                    $agentFee,
                    $deal->id,
                    "Job Deal #{$deal->id} — \"{$jobPost->job_title}\" এর জন্য Escrow hold"
                );
            } catch (WalletException $e) {
                throw ValidationException::withMessages([
                    'job_selection_id' => $e->getMessage(),
                ]);
            }

            // ── Milestone তৈরি: Deal Confirmed হওয়ার সাথে সাথে ৩টি Milestone (settings থেকে %/title) ──
            app(MilestoneService::class)->createForDeal($deal);

            // ── Selection আপডেট ──
            $selection->fill([
                'worker_response'     => 'accepted',
                'worker_responded_at' => now(),
            ])->save();

            // ── Interest status = hired (guarded) ──
            $interest = $selection->interest;
            if ($interest) {
                $interest->forceFill(['status' => 'hired'])->save();
            }

            // ── Job Post filled_count বৃদ্ধি (guarded — increment বাইপাস করে) ──
            $jobPost->increment('filled_count');

            if ($jobPost->fresh()->filled_count >= $jobPost->vacancies) {
                $jobPost->forceFill(['status' => 'filled'])->save();
            }

            return [
                'deal'        => $deal->fresh(),
                'jobPost'     => $jobPost->fresh(),
                'workerModel' => $workerModel,
            ];
        });

        // DB commit হওয়ার পরে উভয় ইভেন্ট notify —
        // escrow_hold_confirmed (Worker + Agent) এবং worker_accepted (Job Agent + Admin)
        $this->notifications->escrowHoldConfirmed($result['deal']);
        $this->notifications->workerAccepted($result['workerModel'], $result['jobPost']);

        return $result['deal'];
    }

    /**
     * Worker একটি pending Selection প্রত্যাখ্যান করলে —
     * কোনো escrow বা wallet টাচ হয় না।
     */
    public function reject(int $selectionId, User $worker): JobSelection
    {
        $result = DB::transaction(function () use ($selectionId, $worker) {

            $selection   = JobSelection::lockForUpdate()->findOrFail($selectionId);
            $workerModel = Worker::where('worker_user_id', $worker->id)->firstOrFail();

            // BUG FIX (Helal-reported, Step 10.9 audit): same PDO
            // string/int strict-comparison bug as select()/accept() above.
            if ((int) $selection->worker_id !== (int) $workerModel->id) {
                throw ValidationException::withMessages([
                    'job_selection_id' => 'এই Selection আপনার জন্য নয়।',
                ]);
            }

            if ($selection->worker_response !== 'pending') {
                throw ValidationException::withMessages([
                    'job_selection_id' => 'এই Selection এর উপর ইতিমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
                ]);
            }

            $selection->fill([
                'worker_response'     => 'rejected',
                'worker_responded_at' => now(),
            ])->save();

            $interest = $selection->interest;
            if ($interest) {
                $interest->forceFill(['status' => 'rejected'])->save();
            }

            return [
                'selection'   => $selection->fresh(),
                'jobPost'     => $selection->jobPost,
                'workerModel' => $workerModel,
            ];
        });

        $this->notifications->workerRejectedSelection($result['workerModel'], $result['jobPost']);

        return $result['selection'];
    }

    /**
     * Scheduler কর্তৃক কল হয় (hourly) —
     * worker_response = 'pending' এবং expires_at পার হয়ে যাওয়া সব Selection
     * auto-expire করে, সংশ্লিষ্ট Interest কে আবার 'pending' এ ফিরিয়ে দেয়
     * (Agent যাতে চাইলে আবার Select করতে পারে), এবং Agent + Worker কে notify করে।
     *
     * কোনো Wallet/Escrow টাচ হয় না — Escrow শুধু accept() এ hold হয়,
     * তাই pending selection expire হওয়ায় wallet এ কোনো প্রভাব নেই।
     */
    public function expirePending(): int
    {
        $expiredCount = 0;

        JobSelection::where('worker_response', 'pending')
            ->where('expires_at', '<', now())
            ->select('id')
            ->chunkById(100, function ($rows) use (&$expiredCount) {
                foreach ($rows as $row) {
                    $expired = DB::transaction(function () use ($row) {

                        $selection = JobSelection::lockForUpdate()->find($row->id);

                        // Race guard: এর মধ্যে অন্য প্রসেস (accept/reject/অন্য scheduler run)
                        // ইতিমধ্যে হ্যান্ডেল করে থাকলে skip
                        if (! $selection
                            || $selection->worker_response !== 'pending'
                            || $selection->expires_at->isFuture()
                        ) {
                            return null;
                        }

                        $selection->fill([
                            'worker_response'     => 'expired',
                            'worker_responded_at' => now(),
                        ])->save();

                        // Interest কে 'pending' এ ফিরিয়ে দিন যাতে Agent আবার Select করতে পারে
                        $interest = $selection->interest;
                        if ($interest && $interest->status === 'selected') {
                            $interest->forceFill(['status' => 'pending'])->save();
                        }

                        return $selection->fresh();
                    });

                    if ($expired) {
                        // worker relation null হতে পারে (agent_created placeholder, claim হয়নি)
                        // — NotificationService::selectionExpired() নিজেই null-guard করে,
                        // তবে সেফটির জন্য এখানেও চেক রাখা হলো।
                        if ($expired->worker) {
                            $this->notifications->selectionExpired($expired->worker, $expired->jobPost);
                        }
                        $expiredCount++;
                    }
                }
            });

        return $expiredCount;
    }
}