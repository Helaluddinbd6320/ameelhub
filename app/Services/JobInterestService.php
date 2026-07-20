<?php

namespace App\Services;

use App\Models\JobFeeReveal;
use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobInterestService
{
    /**
     * WAY 1 — Worker directly expresses interest in a job (interest_source = worker_self).
     * Requires: active Worker CV, active JobPost with open vacancy, and prior fee reveal.
     */
    public function submitWorkerInterest(User $user, JobPost $jobPost, ?string $note = null): JobInterest
    {
        return DB::transaction(function () use ($user, $jobPost, $note) {

            // Lock job post row to avoid race conditions with filled_count/status changes
            $jobPost = JobPost::where('id', $jobPost->id)->lockForUpdate()->firstOrFail();

            $worker = Worker::where('worker_user_id', $user->id)->first();

            if (! $worker) {
                throw ValidationException::withMessages([
                    'worker' => 'আপনার কোনো CV পাওয়া যায়নি।',
                ]);
            }

            if (! in_array($worker->status, ['active', 'featured'], true)) {
                throw ValidationException::withMessages([
                    'worker' => 'আবেদন করার আগে আপনার CV Active হতে হবে।',
                ]);
            }

            if ($jobPost->status !== 'active') {
                throw ValidationException::withMessages([
                    'job' => 'এই জব বর্তমানে সক্রিয় নেই।',
                ]);
            }

            if ($jobPost->expires_at && $jobPost->expires_at->isPast()) {
                throw ValidationException::withMessages([
                    'job' => 'এই জবের মেয়াদ শেষ হয়ে গেছে।',
                ]);
            }

            if ($jobPost->filled_count >= $jobPost->vacancies) {
                throw ValidationException::withMessages([
                    'job' => 'এই জবের সব পদ পূরণ হয়ে গেছে।',
                ]);
            }

            $feeReveal = JobFeeReveal::where('user_id', $user->id)
                ->where('job_post_id', $jobPost->id)
                ->first();

            if (! $feeReveal) {
                throw ValidationException::withMessages([
                    'fee' => 'আবেদন করার আগে Fee reveal করুন।',
                ]);
            }

            $alreadyExists = JobInterest::where('job_post_id', $jobPost->id)
                ->where('worker_id', $worker->id)
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages([
                    'interest' => 'আপনি ইতিমধ্যে এই জবে আবেদন করেছেন।',
                ]);
            }

            $interest = new JobInterest();
            $interest->forceFill([
                'job_post_id'      => $jobPost->id,
                'worker_id'        => $worker->id,
                'user_id'          => $user->id,
                'interested_by_id' => null, // self-applied, no agent involved
                'fee_reveal_id'    => $feeReveal->id,
                'interest_note'    => $note,
                'interest_source'  => 'worker_self',
                'nok_id'           => null,
                'status'           => 'pending',
                'interested_at'    => now(),
            ])->save();

            // TODO (Phase 9): fire InterestReceived notification to $jobPost->posted_by_id

            return $interest;
        });
    }
}