<?php

namespace App\Livewire;

use App\Exceptions\WalletException;
use App\Models\AgentProfile;
use App\Models\JobFeeReveal;
use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\Worker;
use App\Services\JobFeeRevealService;
use App\Services\SeoService;
use App\Services\WorkerAccountService;
use Illuminate\Database\QueryException;
use Livewire\Component;

class JobDetail extends Component
{
    public JobPost $job;

    public bool $hasRevealedFee = false;
    public ?string $revealedFeeAmount = null;

    // ─── Worker Direct Interest (Step 5.1 — Way 1) ─────────────────────
    public bool $hasExpressedInterest = false;
    public ?string $interestNote = null;

    public ?int $workerCvId = null;
    public bool $workerCvEligible = false;

    // ─── Agent Interest / CV Select Modal (Step 5.2 — Way 3) ───────────
    public bool $isAgent = false;
    public bool $isVerifiedAgent = false;
    public bool $isOwnJobPost = false;

    public bool $showWorkerModal = false;
    public string $workerSearch = '';
    public ?int $selectedAgentWorkerId = null;
    public ?string $selectedAgentWorkerName = null;
    public ?string $agentInterestNote = null;

    public function mount(string $uuid, JobFeeRevealService $feeRevealService, SeoService $seoService): void
    {
        $this->job = JobPost::with('skillCategory')
            ->where('uuid', $uuid)
            ->firstOrFail();

        abort_unless($this->job->isVisible(), 404);

        view()->share('seo', $seoService->job($this->job));

        $this->job->forceFill(['view_count' => $this->job->view_count + 1])->save();

        if (auth()->check()) {
            $user = auth()->user();

            $this->hasRevealedFee = $feeRevealService->hasRevealed($this->job, $user);

            if ($this->hasRevealedFee) {
                $this->revealedFeeAmount = $this->job->agent_fee_sar;
            }

            if ($user->role === 'worker') {
                $worker = Worker::where('worker_user_id', $user->id)->first();

                if ($worker) {
                    $this->workerCvId = $worker->id;
                    $this->workerCvEligible = in_array($worker->status, ['active', 'featured'], true);

                    $this->hasExpressedInterest = JobInterest::where('job_post_id', $this->job->id)
                        ->where('worker_id', $worker->id)
                        ->exists();
                }
            }

            if ($user->role === 'agent') {
                $this->isAgent = true;
                $this->isOwnJobPost = $this->job->posted_by_id === $user->id;

                $agentProfile = AgentProfile::where('user_id', $user->id)->first();
                $this->isVerifiedAgent = (bool) ($agentProfile?->is_verified);
            }
        }
    }

    public function revealFee(JobFeeRevealService $feeRevealService)
    {
        if (! auth()->check()) {
            session()->flash('job_error', 'ফি দেখতে হলে আগে লগইন করুন।');
            return redirect()->route('login');
        }

        try {
            $feeRevealService->reveal($this->job, auth()->user());
        } catch (WalletException $e) {
            session()->flash('job_error', $e->getMessage() . ' — অনুগ্রহ করে Wallet রিচার্জ করুন।');
            return;
        }

        $this->hasRevealedFee = true;
        $this->revealedFeeAmount = $this->job->fresh()->agent_fee_sar;
    }

    public function submitInterest()
    {
        if (! auth()->check()) {
            session()->flash('job_error', 'আগ্রহ প্রকাশ করতে আগে লগইন করুন।');
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'worker') {
            session()->flash('job_error', 'শুধুমাত্র Worker একাউন্ট থেকে আগ্রহ প্রকাশ করা যায়।');
            return;
        }

        if (! $this->hasRevealedFee) {
            session()->flash('job_error', 'আগ্রহ প্রকাশের আগে Agent Fee দেখতে হবে।');
            return;
        }

        if (! $this->workerCvId || ! $this->workerCvEligible) {
            session()->flash('job_error', 'আপনার CV এখনো Active নয়, তাই আগ্রহ প্রকাশ করা যাবে না।');
            return;
        }

        if ($this->hasExpressedInterest) {
            session()->flash('job_error', 'আপনি ইতিমধ্যে এই জবে আগ্রহ প্রকাশ করেছেন।');
            return;
        }

        try {
            \DB::transaction(function () use ($user) {
                // Re-fetch job fresh + locked — job could've been closed/filled/expired
                // while the user was sitting on this page.
                $job = JobPost::where('id', $this->job->id)->lockForUpdate()->firstOrFail();

                if ($job->status !== 'active') {
                    throw new \RuntimeException('এই জব বর্তমানে সক্রিয় নেই।');
                }

                if ($job->expires_at && $job->expires_at->isPast()) {
                    throw new \RuntimeException('এই জবের মেয়াদ শেষ হয়ে গেছে।');
                }

                if ($job->filled_count >= $job->vacancies) {
                    throw new \RuntimeException('এই জবের সব পদ পূরণ হয়ে গেছে।');
                }

                $feeReveal = JobFeeReveal::where('user_id', $user->id)
                    ->where('job_post_id', $job->id)
                    ->first();

                $interest = new JobInterest();
                $interest->fill([
                    'job_post_id'     => $job->id,
                    'worker_id'       => $this->workerCvId,
                    'user_id'         => $user->id,
                    'interest_note'   => $this->interestNote,
                    'interest_source' => 'worker_self',
                ]);
                $interest->forceFill([
                    'interested_by_id' => null, // self-applied, no agent involved
                    'fee_reveal_id'    => $feeReveal?->id,
                    'nok_id'           => null,
                    'status'           => 'pending',
                    'interested_at'    => now(),
                ])->save();
            });

            $this->hasExpressedInterest = true;
            session()->flash('job_success', 'আপনার আগ্রহ সফলভাবে জমা হয়েছে।');

            // TODO (Phase 9): interest_received notification event
        } catch (QueryException $e) {
            $this->hasExpressedInterest = true;
            session()->flash('job_error', 'আপনি ইতিমধ্যে এই জবে আগ্রহ প্রকাশ করেছেন।');
        } catch (\RuntimeException $e) {
            session()->flash('job_error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // AGENT INTEREST — CV Select Modal (Step 5.2 — Way 3)
    // ─────────────────────────────────────────────────────────────────

    public function openWorkerModal()
    {
        if (! auth()->check() || auth()->user()->role !== 'agent') {
            session()->flash('job_error', 'শুধুমাত্র Agent একাউন্ট থেকে এই অ্যাকশন করা যায়।');
            return;
        }

        if (! $this->isVerifiedAgent) {
            session()->flash('job_error', 'আপনার Agent একাউন্ট এখনো Verified নয়। Verification সম্পন্ন করুন।');
            return;
        }

        if ($this->isOwnJobPost) {
            session()->flash('job_error', 'এটি আপনার নিজের জব। নিজের জবের জন্য "Worker খুঁজুন" (Nok) ব্যবহার করুন।');
            return;
        }

        if (! $this->hasRevealedFee) {
            session()->flash('job_error', 'Worker সাবমিট করার আগে Agent Fee দেখতে হবে।');
            return;
        }

        $this->reset(['workerSearch', 'selectedAgentWorkerId', 'selectedAgentWorkerName', 'agentInterestNote']);
        $this->showWorkerModal = true;
    }

    public function closeWorkerModal()
    {
        $this->showWorkerModal = false;
        $this->reset(['workerSearch', 'selectedAgentWorkerId', 'selectedAgentWorkerName', 'agentInterestNote']);
    }

    public function selectWorker(int $workerId, string $workerName)
    {
        $this->selectedAgentWorkerId = $workerId;
        $this->selectedAgentWorkerName = $workerName;
    }

    public function getWorkerSearchResultsProperty()
    {
        if (! $this->showWorkerModal || mb_strlen(trim($this->workerSearch)) < 2) {
            return collect();
        }

        $existingWorkerIds = JobInterest::where('job_post_id', $this->job->id)
            ->pluck('worker_id');

        return Worker::query()
            ->where('submitted_by_id', auth()->id())
            ->whereIn('status', ['active', 'featured'])
            ->where(function ($q) {
                $q->where('full_name_bn', 'like', '%' . $this->workerSearch . '%')
                    ->orWhere('full_name_en', 'like', '%' . $this->workerSearch . '%')
                    ->orWhereHas('skillCategory', function ($sq) {
                        $sq->where('name_bn', 'like', '%' . $this->workerSearch . '%')
                            ->orWhere('name_en', 'like', '%' . $this->workerSearch . '%');
                    });
            })
            ->with('skillCategory')
            ->limit(10)
            ->get()
            ->map(function ($worker) use ($existingWorkerIds) {
                $worker->already_applied = $existingWorkerIds->contains($worker->id);
                return $worker;
            });
    }

    public function submitAgentInterest(WorkerAccountService $workerAccounts)
    {
        if (! auth()->check()) {
            session()->flash('job_error', 'আগে লগইন করুন।');
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'agent') {
            session()->flash('job_error', 'শুধুমাত্র Agent একাউন্ট থেকে এই অ্যাকশন করা যায়।');
            return;
        }

        if (! $this->isVerifiedAgent) {
            session()->flash('job_error', 'আপনার Agent একাউন্ট এখনো Verified নয়।');
            return;
        }

        if ($this->isOwnJobPost) {
            session()->flash('job_error', 'এটি আপনার নিজের জব। Nok সিস্টেম ব্যবহার করুন।');
            $this->closeWorkerModal();
            return;
        }

        if (! $this->hasRevealedFee) {
            session()->flash('job_error', 'Worker সাবমিট করার আগে Agent Fee দেখতে হবে।');
            return;
        }

        if (! $this->selectedAgentWorkerId) {
            session()->flash('job_error', 'অনুগ্রহ করে একজন Worker সিলেক্ট করুন।');
            return;
        }

        $worker = Worker::find($this->selectedAgentWorkerId);

        if (! $worker || ! in_array($worker->status, ['active', 'featured'], true)) {
            session()->flash('job_error', 'নির্বাচিত Worker বর্তমানে Active নয়।');
            $this->closeWorkerModal();
            return;
        }

        if ($worker->submitted_by_id !== $user->id) {
            session()->flash('job_error', 'আপনি শুধুমাত্র নিজের সাবমিট করা Worker CV দিয়ে আগ্রহ পাঠাতে পারবেন।');
            $this->closeWorkerModal();
            return;
        }

        // Every Worker CV must have a linked User account by this point.
        // Normally the WorkerObserver already created one at CV-creation
        // time; this call is a safety net for any legacy/pre-migration CVs.
        $workerUser = $workerAccounts->ensureUserAccount($worker);

        try {
            \DB::transaction(function () use ($worker, $user, $workerUser) {
                $job = JobPost::where('id', $this->job->id)->lockForUpdate()->firstOrFail();

                if ($job->status !== 'active') {
                    throw new \RuntimeException('এই জব বর্তমানে সক্রিয় নেই।');
                }

                if ($job->expires_at && $job->expires_at->isPast()) {
                    throw new \RuntimeException('এই জবের মেয়াদ শেষ হয়ে গেছে।');
                }

                if ($job->filled_count >= $job->vacancies) {
                    throw new \RuntimeException('এই জবের সব পদ পূরণ হয়ে গেছে।');
                }

                $feeReveal = JobFeeReveal::where('user_id', $user->id)
                    ->where('job_post_id', $job->id)
                    ->first();

                $interest = new JobInterest();
                $interest->fill([
                    'job_post_id'     => $job->id,
                    'worker_id'       => $worker->id,
                    'user_id'         => $workerUser->id,
                    'interest_note'   => $this->agentInterestNote,
                    'interest_source' => 'agent_select',
                ]);
                $interest->forceFill([
                    'interested_by_id' => $user->id,
                    'fee_reveal_id'    => $feeReveal?->id,
                    'nok_id'           => null,
                    'status'           => 'pending',
                    'interested_at'    => now(),
                ])->save();
            });

            session()->flash('job_success', 'Worker এর পক্ষে আগ্রহ সফলভাবে জমা হয়েছে।');
            $this->closeWorkerModal();

            // TODO (Phase 9): interest_received notification event (job-poster agent)
            // TODO (Phase 9): notify the selected worker that an agent applied on their behalf
        } catch (QueryException $e) {
            session()->flash('job_error', 'এই Worker ইতিমধ্যে এই জবে আগ্রহ প্রকাশ করেছেন।');
        } catch (\RuntimeException $e) {
            session()->flash('job_error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.job-detail')->layout('layouts.app');
    }
}