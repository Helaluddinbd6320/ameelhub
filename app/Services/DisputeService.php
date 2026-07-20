<?php

namespace App\Services;

use App\Exceptions\WalletException;
use App\Models\DisputeEvidence;
use App\Models\JobDeal;
use App\Models\JobDealMilestone;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DisputeService
{
    public function __construct(
        protected WalletService $walletService,
        protected NotificationService $notifications
    ) {}

    // ─────────────────────────────────────────────
    // SECTION 1 — Raise Dispute
    // ─────────────────────────────────────────────

    /**
     * Worker বা Agent কর্তৃক Dispute Raise — নির্দিষ্ট Milestone এর উপর।
     * - Deal-এর সাথে সম্পর্কিত পক্ষ (worker/agent) ছাড়া কেউ raise করতে পারবে না
     * - দিনে সর্বোচ্চ ২টি dispute/user (settings: disputes_daily_limit)
     * - ইতিমধ্যে disputed থাকলে আবার raise করা যাবে না
     * - Milestone অবশ্যই admin_released হওয়া যাবে না (রিলিজ হয়ে গেলে dispute অচল)
     * - Evidence ফাইল থাকলে সেগুলো storeEvidence() দিয়ে সংরক্ষণ হবে
     */
    public function raise(
        JobDealMilestone $milestone,
        User $user,
        string $reason,
        array $files = []
    ): JobDealMilestone {
        $milestone = DB::transaction(function () use ($milestone, $user, $reason) {
            $milestone = JobDealMilestone::where('id', $milestone->id)->lockForUpdate()->firstOrFail();
            $deal = JobDeal::where('id', $milestone->job_deal_id)->lockForUpdate()->firstOrFail();

            $role = $this->determineRole($deal, $user);

            if (! $role) {
                throw ValidationException::withMessages([
                    'dispute' => 'আপনি এই ডিলের সাথে সম্পর্কিত নন, তাই বিরোধ (dispute) তোলার অনুমতি নেই।',
                ]);
            }

            if ($milestone->isDisputed()) {
                throw ValidationException::withMessages([
                    'dispute' => 'এই মাইলস্টোনে ইতিমধ্যে একটি বিরোধ চলমান আছে।',
                ]);
            }

            if ($milestone->isReleased()) {
                throw ValidationException::withMessages([
                    'dispute' => 'এই মাইলস্টোন ইতিমধ্যে রিলিজ হয়ে গেছে, তাই এখন বিরোধ তোলা যাবে না।',
                ]);
            }

            $dailyLimit = (int) Setting::get('disputes_daily_limit', 2);
            $todayCount = $this->countDisputesRaisedToday($user);

            if ($todayCount >= $dailyLimit) {
                throw ValidationException::withMessages([
                    'dispute' => "আজকে আপনি ইতিমধ্যে সর্বোচ্চ {$dailyLimit}টি বিরোধ তুলেছেন। আগামীকাল আবার চেষ্টা করুন।",
                ]);
            }

            $milestone->forceFill([
                'status'            => 'disputed',
                'dispute_raised_by' => $role,
                'dispute_reason'    => $reason,
                'dispute_raised_at' => now(),
            ])->save();

            $deal->forceFill([
                'status'             => 'disputed',
                'dispute_raised_by'  => $role,
                'dispute_reason'     => $reason,
            ])->save();

            return $milestone->fresh();
        });

        if (! empty($files)) {
            $this->storeEvidence($milestone, $user, $files);
        }

        $this->notifications->disputeRaised($milestone->fresh());

        return $milestone->fresh();
    }

    // ─────────────────────────────────────────────
    // SECTION 2 — Resolve Dispute (Admin)
    // ─────────────────────────────────────────────

    /**
     * Admin কর্তৃক Dispute Resolution।
     *
     * $resolution অপশন:
     *   'full_refund'  → Worker এর held balance পুরোপুরি ফেরত (available এ)
     *   'full_release' → Agent সম্পূর্ণ amount পায় (normal release এর মতোই)
     *   'partial'      → $partialWorkerPct% Worker কে ফেরত, বাকিটা Agent কে (কমিশন কাটার পর)
     *
     * Deal status: dispute resolve হওয়ার পর আগের অবস্থায় ফিরবে (working/confirmed অনুযায়ী)
     * যদি এটাই শেষ pending milestone হয়, deal completed/cancelled মার্ক হবে যথাক্রমে।
     */
    public function resolve(
        JobDealMilestone $milestone,
        string $resolution,
        string $notes,
        User $admin,
        ?float $partialWorkerPct = null
    ): JobDealMilestone {
        $milestone = DB::transaction(function () use ($milestone, $resolution, $notes, $admin, $partialWorkerPct) {
            $milestone = JobDealMilestone::where('id', $milestone->id)->lockForUpdate()->firstOrFail();
            $deal = JobDeal::where('id', $milestone->job_deal_id)->lockForUpdate()->firstOrFail();

            if (! $milestone->isDisputed()) {
                throw ValidationException::withMessages([
                    'dispute' => 'এই মাইলস্টোনে কোনো চলমান বিরোধ নেই।',
                ]);
            }

            $workerUser = User::find($deal->worker->worker_user_id);

            if (! $workerUser) {
                throw ValidationException::withMessages([
                    'dispute' => 'Worker এর ইউজার অ্যাকাউন্ট খুঁজে পাওয়া যায়নি।',
                ]);
            }

            try {
                match ($resolution) {
                    'full_refund'  => $this->applyFullRefund($milestone, $deal, $workerUser, $admin),
                    'full_release' => $this->applyFullRelease($milestone, $deal, $workerUser, $admin),
                    'partial'      => $this->applyPartial($milestone, $deal, $workerUser, $admin, $partialWorkerPct),
                    default        => throw ValidationException::withMessages([
                        'dispute' => 'অবৈধ resolution টাইপ। full_refund, full_release অথবা partial ব্যবহার করুন।',
                    ]),
                };
            } catch (WalletException $e) {
                throw ValidationException::withMessages([
                    'dispute' => $e->getMessage(),
                ]);
            }

            $resolutionMap = [
                'full_refund'  => 'refunded',
                'full_release' => 'released',
                'partial'      => 'partial',
            ];

            $milestone->forceFill([
                'status'           => 'admin_released', // dispute বন্ধ — resolution ফিল্ডে refunded/released/partial এর ভেদ থাকবে
                'resolution'       => $resolutionMap[$resolution],
                'resolution_notes' => $notes,
                'resolved_by_id'   => $admin->id,
                'resolved_at'      => now(),
            ])->save();

            $this->settleDealAfterDispute($deal, $milestone, $resolution, $notes);

            return $milestone->fresh();
        });

        $this->notifications->disputeResolved($milestone);

        return $milestone;
    }

    /**
     * Extend Deadline — কোনো টাকা movement হয় না, শুধু dispute note আপডেট হয়
     * এবং milestone আবার 'agent_confirmed' বা যথাযথ আগের status এ ফিরে যায় যাতে
     * উভয় পক্ষ নতুন সময়সীমার মধ্যে আবার confirm করতে পারে।
     *
     * NOTE: Section 16 তে "Extend Deadline" এর জন্য আলাদা কোনো notification
     * event নেই (শুধু dispute_raised/dispute_resolved আছে) — তাই এখানে
     * ইচ্ছাকৃতভাবে কোনো NotificationService কল যোগ করা হয়নি। প্রয়োজন হলে
     * জানাবেন, একটা dispute_extended ইভেন্ট যোগ করে দেওয়া যাবে।
     */
    public function extendDeadline(
        JobDealMilestone $milestone,
        string $notes,
        User $admin,
        string $revertToStatus = 'pending'
    ): JobDealMilestone {
        return DB::transaction(function () use ($milestone, $notes, $admin, $revertToStatus) {
            $milestone = JobDealMilestone::where('id', $milestone->id)->lockForUpdate()->firstOrFail();
            $deal = JobDeal::where('id', $milestone->job_deal_id)->lockForUpdate()->firstOrFail();

            if (! $milestone->isDisputed()) {
                throw ValidationException::withMessages([
                    'dispute' => 'এই মাইলস্টোনে কোনো চলমান বিরোধ নেই।',
                ]);
            }

            $milestone->forceFill([
                'status'            => $revertToStatus,
                'resolution'        => null,
                'resolution_notes'  => $notes,
                'resolved_by_id'    => $admin->id,
                'resolved_at'       => now(),
                'worker_confirmed_at' => $revertToStatus === 'pending' ? null : $milestone->worker_confirmed_at,
                'agent_confirmed_at'  => in_array($revertToStatus, ['pending', 'worker_confirmed']) ? null : $milestone->agent_confirmed_at,
            ])->save();

            $deal->forceFill([
                'status'          => 'working',
                'admin_notes'     => trim(($deal->admin_notes ?? '') . "\n[" . now() . "] সময়সীমা বর্ধিত: {$notes}"),
            ])->save();

            return $milestone->fresh();
        });
    }

    // ─────────────────────────────────────────────
    // SECTION 3 — Evidence Upload
    // ─────────────────────────────────────────────

    /**
     * Dispute Evidence আপলোড — max 5MB/file, max 5 files/milestone (মোট, সব upload মিলিয়ে)
     * ULID filename, private disk (admin-only access)
     */
    public function storeEvidence(JobDealMilestone $milestone, User $user, array $files): array
    {
        $deal = $milestone->deal()->firstOrFail();
        $role = $this->determineRole($deal, $user);

        if (! $role) {
            throw ValidationException::withMessages([
                'evidence' => 'আপনি এই ডিলের সাথে সম্পর্কিত নন, তাই evidence আপলোড করতে পারবেন না।',
            ]);
        }

        $existingCount = DisputeEvidence::where('milestone_id', $milestone->id)->count();
        $incomingCount = count($files);

        if ($existingCount + $incomingCount > 5) {
            throw ValidationException::withMessages([
                'evidence' => "প্রতি মাইলস্টোনে সর্বোচ্চ ৫টি evidence ফাইল আপলোড করা যাবে। বর্তমানে {$existingCount}টি আছে।",
            ]);
        }

        $maxSizeBytes = 5 * 1024 * 1024; // 5MB
        $allowedMimes = [
            'image/jpeg' => 'image',
            'image/png'  => 'image',
            'image/webp' => 'image',
            'application/pdf' => 'pdf',
        ];

        $stored = [];

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                throw ValidationException::withMessages([
                    'evidence' => 'অবৈধ ফাইল টাইপ।',
                ]);
            }

            if ($file->getSize() > $maxSizeBytes) {
                throw ValidationException::withMessages([
                    'evidence' => "ফাইল '{$file->getClientOriginalName()}' এর সাইজ 5MB এর বেশি হতে পারবে না।",
                ]);
            }

            $mime = $file->getMimeType();

            if (! array_key_exists($mime, $allowedMimes)) {
                throw ValidationException::withMessages([
                    'evidence' => "ফাইল '{$file->getClientOriginalName()}' — শুধুমাত্র image (jpg/png/webp) অথবা PDF আপলোড করা যাবে।",
                ]);
            }

            $extension = $file->getClientOriginalExtension() ?: match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                default => 'bin',
            };

            $filename = 'dispute-evidence/' . Str::ulid() . '.' . $extension;

            Storage::disk('private_docs')->put($filename, file_get_contents($file->getRealPath()));

            $stored[] = DisputeEvidence::create([
                'milestone_id'      => $milestone->id,
                'uploaded_by_id'    => $user->id,
                'uploaded_by_role'  => $role,
                'file_path'         => $filename,
                'file_type'         => $allowedMimes[$mime],
                'description'       => null,
            ]);
        }

        Log::channel('daily')->info('DisputeEvidence uploaded', [
            'milestone_id' => $milestone->id,
            'user_id'      => $user->id,
            'count'        => count($stored),
        ]);

        return $stored;
    }

    // ─────────────────────────────────────────────
    // SECTION 4 — Private Resolution Handlers
    // ─────────────────────────────────────────────

    /** Full Refund: Worker এর held balance পুরোপুরি available এ ফেরত */
    private function applyFullRefund(JobDealMilestone $milestone, JobDeal $deal, User $workerUser, User $admin): void
    {
        $this->walletService->release(
            $workerUser,
            (float) $milestone->amount_sar,
            $deal->id,
            "Deal #{$deal->id} — Milestone #{$milestone->id} dispute full refund",
            $admin
        );
    }

    /** Full Release: স্বাভাবিক release এর মতোই — Worker held থেকে deduct, Agent কে credit */
    private function applyFullRelease(JobDealMilestone $milestone, JobDeal $deal, User $workerUser, User $admin): void
    {
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

        $milestone->forceFill([
            'admin_released_at' => now(),
            'released_by_id'    => $admin->id,
        ])->save();
    }

    /**
     * Partial: $partialWorkerPct% Worker কে ফেরত (held থেকে available),
     * বাকি অংশ Agent কে কমিশন কেটে credit।
     */
    private function applyPartial(
        JobDealMilestone $milestone,
        JobDeal $deal,
        User $workerUser,
        User $admin,
        ?float $partialWorkerPct
    ): void {
        if ($partialWorkerPct === null || $partialWorkerPct < 0 || $partialWorkerPct > 100) {
            throw ValidationException::withMessages([
                'dispute' => 'Partial resolution এর জন্য worker_pct (0-100) দিতে হবে।',
            ]);
        }

        $totalAmount   = (float) $milestone->amount_sar;
        $workerRefund  = round($totalAmount * $partialWorkerPct / 100, 2);
        $agentPortion  = round($totalAmount - $workerRefund, 2);
        $commissionPct = (float) $deal->chapai_commission_pct;
        $agentCommission = round($agentPortion * $commissionPct / 100, 2);
        $agentReceives   = round($agentPortion - $agentCommission, 2);

        if ($workerRefund > 0) {
            $this->walletService->release(
                $workerUser,
                $workerRefund,
                $deal->id,
                "Deal #{$deal->id} — Milestone #{$milestone->id} dispute partial refund ({$partialWorkerPct}%)",
                $admin
            );
        }

        if ($agentPortion > 0) {
            // Worker এর held থেকে agent-portion চূড়ান্তভাবে deduct
            $this->walletService->deductHeld(
                $workerUser,
                $agentPortion,
                $deal->id,
                $milestone->id,
                $admin
            );

            if ($agentReceives > 0) {
                $this->walletService->creditAgent(
                    $deal->agent,
                    $agentReceives,
                    $deal->id,
                    $milestone->id,
                    $admin
                );
            }
        }

        $milestone->forceFill([
            'admin_released_at' => now(),
            'released_by_id'    => $admin->id,
        ])->save();
    }

    /** Dispute resolve হওয়ার পর Deal-কে যথাযথ status এ ফিরিয়ে আনে */
    private function settleDealAfterDispute(
        JobDeal $deal,
        JobDealMilestone $milestone,
        string $resolution,
        string $notes
    ): void {
        $isLastMilestone = $milestone->milestone_number === 3;

        if ($resolution === 'full_refund') {
            $deal->forceFill([
                'status'           => 'refunded',
                'cancelled_at'     => now(),
                'resolution_notes' => $notes,
            ])->save();

            return;
        }

        if ($isLastMilestone) {
            $deal->forceFill([
                'status'           => 'completed',
                'completed_at'     => now(),
                'resolution_notes' => $notes,
            ])->save();

            return;
        }

        $deal->forceFill([
            'status'           => 'working',
            'resolution_notes' => $notes,
        ])->save();
    }

    // ─────────────────────────────────────────────
    // SECTION 5 — Helpers
    // ─────────────────────────────────────────────

    private function determineRole(JobDeal $deal, User $user): ?string
    {
        if ($deal->agent_id === $user->id) {
            return 'agent';
        }

        if ($deal->worker && $deal->worker->worker_user_id === $user->id) {
            return 'worker';
        }

        return null;
    }

    private function countDisputesRaisedToday(User $user): int
    {
        return JobDealMilestone::whereDate('dispute_raised_at', now()->toDateString())
            ->whereHas('deal', function ($query) use ($user) {
                $query->where('agent_id', $user->id)
                    ->orWhereHas('worker', function ($q) use ($user) {
                        $q->where('worker_user_id', $user->id);
                    });
            })
            ->count();
    }
}