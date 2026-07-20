<?php

namespace App\Services;

use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobLifecycleService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    /**
     * Auto-close jobs where filled_count >= vacancies.
     * Runs hourly via Scheduler.
     */
    public function autoCloseFilled(): int
    {
        $count = 0;

        JobPost::query()
            ->where('status', 'active')
            ->whereColumn('filled_count', '>=', 'vacancies')
            ->chunkById(50, function ($jobs) use (&$count) {
                foreach ($jobs as $job) {
                    // Idempotency guard
                    if ($job->status === 'filled') {
                        continue;
                    }

                    DB::transaction(function () use ($job) {
                        $job->forceFill([
                            'status'    => 'filled',
                            'closed_at' => now(),
                        ])->save();
                    });

                    $fresh = $job->fresh();

                    $this->notifications->jobFilledAuto($fresh);
                    $this->notifyOnClose($fresh);
                    $count++;
                }
            });

        Log::info("JobLifecycleService::autoCloseFilled — {$count} jobs marked filled");

        return $count;
    }

    /**
     * Auto-close jobs whose expires_at date has passed.
     * Runs daily via Scheduler.
     */
    public function autoCloseExpired(): int
    {
        $count = 0;

        JobPost::query()
            ->whereIn('status', ['active', 'paused'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', Carbon::today())
            ->chunkById(50, function ($jobs) use (&$count) {
                foreach ($jobs as $job) {
                    // Idempotency guard
                    if ($job->status === 'closed') {
                        continue;
                    }

                    DB::transaction(function () use ($job) {
                        $job->forceFill([
                            'status'       => 'closed',
                            'close_reason' => 'Auto-closed: expiry date passed',
                            'closed_at'    => now(),
                        ])->save();
                    });

                    $fresh = $job->fresh();

                    $this->notifications->jobAutoClosed($fresh);
                    $this->notifyOnClose($fresh);
                    $count++;
                }
            });

        Log::info("JobLifecycleService::autoCloseExpired — {$count} jobs auto-closed");

        return $count;
    }

    /**
     * Alert agents whose active jobs are "stale" — older than
     * settings('job_auto_close_days') with no interest yet.
     * Runs daily via Scheduler.
     *
     * NOTE: Assumes a `setting()` helper / Setting model exists from
     * the settings table (Section 14, #19). If not yet built, replace
     * the DB::table('settings') line with your actual Setting accessor.
     */
    public function alertStale(): int
    {
        $days = (int) (DB::table('settings')->where('key', 'job_auto_close_days')->value('value') ?? 90);

        $staleJobs = JobPost::query()
            ->where('status', 'active')
            ->where('created_at', '<', now()->subDays($days))
            ->doesntHave('interests') // relationship must exist on JobPost model
            ->get();

        foreach ($staleJobs as $job) {
            $this->notifications->jobStaleAlert($job);
        }

        return $staleJobs->count();
    }

    /**
     * Notify all pending job_interests when a job is closed/filled.
     * প্রতিটি pending job_interest এর user_id-কে জানানো হয় যে তাদের
     * আবেদন করা Job এখন বন্ধ হয়ে গেছে।
     */
    public function notifyOnClose(JobPost $job): void
    {
        $pendingInterests = JobInterest::where('job_post_id', $job->id)
            ->where('status', 'pending')
            ->get();

        foreach ($pendingInterests as $interest) {
            $user = User::find($interest->user_id);

            if ($user) {
                $this->notifications->jobClosedForInterestedParty($job, $user);
            }
        }

        Log::info("notifyOnClose fired for job_post_id={$job->id}, status={$job->status}, notified={$pendingInterests->count()}");
    }
}