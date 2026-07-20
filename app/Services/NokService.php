<?php

namespace App\Services;

use App\Models\AgentNok;
use App\Models\JobInterest;
use App\Models\JobPost;
use App\Models\Setting;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NokService
{
    public function __construct(
        protected NotificationService $notifications
    ) {}

    /**
     * Route A / Route B — একজন Worker কে একটি Job এর জন্য Nok পাঠানো।
     */
    public function send(int $jobPostId, int $workerId, ?string $message, string $route = 'route_a'): AgentNok
    {
        $nok = DB::transaction(function () use ($jobPostId, $workerId, $message, $route) {

            $jobPost = JobPost::lockForUpdate()->findOrFail($jobPostId);
            $worker  = Worker::lockForUpdate()->findOrFail($workerId);

            if ($route === 'route_a' && $jobPost->posted_by_id !== auth()->id()) {
                throw ValidationException::withMessages([
                    'job_post_id' => 'আপনি শুধুমাত্র নিজের পোস্ট করা Job থেকে Nok পাঠাতে পারবেন।',
                ]);
            }

            if ($jobPost->status !== 'active') {
                throw ValidationException::withMessages([
                    'job_post_id' => 'এই Job বর্তমানে সক্রিয় নয়, Nok পাঠানো যাবে না।',
                ]);
            }

            if ($jobPost->filled_count >= $jobPost->vacancies) {
                throw ValidationException::withMessages([
                    'job_post_id' => 'এই Job এর সব পদ ইতিমধ্যে পূরণ হয়ে গেছে।',
                ]);
            }

            if ($worker->status !== 'active') {
                throw ValidationException::withMessages([
                    'worker_id' => 'এই Worker এর CV বর্তমানে সক্রিয় নয়, Nok পাঠানো যাবে না।',
                ]);
            }

            $existing = AgentNok::where('job_post_id', $jobPost->id)
                ->where('agent_id', auth()->id())
                ->where('worker_id', $worker->id)
                ->first();

            if ($existing) {
                $reason = match ($existing->status) {
                    'pending'  => 'এই Worker কে ইতিমধ্যে Nok পাঠানো হয়েছে, উত্তরের অপেক্ষায় আছে।',
                    'accepted' => 'এই Worker ইতিমধ্যে আপনার Nok গ্রহণ করেছে।',
                    'rejected' => 'এই Worker আগেই এই Job এর জন্য আপনার Nok প্রত্যাখ্যান করেছে, আবার পাঠানো যাবে না।',
                    'expired'  => 'আগে পাঠানো Nok মেয়াদোত্তীর্ণ হয়েছে, একই Job এর জন্য এই Worker কে আবার পাঠানো যাবে না।',
                    default    => 'এই Worker কে ইতিমধ্যে Nok পাঠানো হয়েছে।',
                };

                throw ValidationException::withMessages(['worker_id' => $reason]);
            }

            $dailyLimit = (int) Setting::get('nok_daily_limit', 10);

            $todayCount = AgentNok::where('job_post_id', $jobPost->id)
                ->where('agent_id', auth()->id())
                ->whereDate('sent_at', now()->toDateString())
                ->count();

            if ($todayCount >= $dailyLimit) {
                throw ValidationException::withMessages([
                    'job_post_id' => "আজকের জন্য এই Job এ Nok পাঠানোর সীমা ({$dailyLimit}টি) শেষ হয়ে গেছে।",
                ]);
            }

            $expireHours = (int) Setting::get('nok_expire_hours', 48);

            $nok = AgentNok::create([
                'job_post_id'    => $jobPost->id,
                'agent_id'       => auth()->id(),
                'worker_id'      => $worker->id,
                'worker_user_id' => $worker->worker_user_id,
                'nok_message'    => $message,
                'route_source'   => $route,
                'status'         => 'pending',
                'sent_at'        => now(),
                'expires_at'     => now()->addHours($expireHours),
            ]);

            return $nok;
        });

        // DB commit হওয়ার পরে notification — transaction rollback হলে ভুল notify এড়াতে।
        $this->notifications->nokSent($nok);

        return $nok;
    }

    /**
     * Bulk Nok — একসাথে একাধিক (max: settings.nok_bulk_max) Worker কে Nok পাঠানো।
     */
    public function sendBulk(int $jobPostId, array $workerIds, ?string $message, string $route = 'route_a'): array
    {
        $bulkMax = (int) Setting::get('nok_bulk_max', 5);

        if (count($workerIds) > $bulkMax) {
            throw ValidationException::withMessages([
                'worker_ids' => "একসাথে সর্বোচ্চ {$bulkMax} জন Worker কে Nok পাঠানো যাবে। আপনি {$this->countLabel($workerIds)}টি নির্বাচন করেছেন।",
            ]);
        }

        if (count($workerIds) === 0) {
            throw ValidationException::withMessages([
                'worker_ids' => 'অন্তত একজন Worker নির্বাচন করুন।',
            ]);
        }

        $workerNames = Worker::whereIn('id', $workerIds)
            ->get(['id', 'full_name_bn', 'full_name_en'])
            ->keyBy('id');

        $results = [];

        foreach ($workerIds as $workerId) {
            $name = $workerNames->get($workerId)?->full_name_bn
                ?? $workerNames->get($workerId)?->full_name_en
                ?? "Worker #{$workerId}";

            try {
                $this->send($jobPostId, $workerId, $message, $route);

                $results[] = [
                    'worker_id'   => $workerId,
                    'worker_name' => $name,
                    'status'      => 'success',
                    'reason'      => null,
                ];
            } catch (ValidationException $e) {
                $results[] = [
                    'worker_id'   => $workerId,
                    'worker_name' => $name,
                    'status'      => 'failed',
                    'reason'      => collect($e->errors())->flatten()->first(),
                ];
            }
        }

        return $results;
    }

    /**
     * Worker কর্তৃক Nok Accept — job_interests এ একটি নতুন রেকর্ড তৈরি করে
     * (interest_source = agent_nok)।
     */
    public function accept(int $nokId, User $worker): JobInterest
    {
        $interest = DB::transaction(function () use ($nokId, $worker) {

            $nok = AgentNok::lockForUpdate()->findOrFail($nokId);

            $workerProfile = Worker::where('worker_user_id', $worker->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($nok->worker_id !== $workerProfile->id) {
                throw ValidationException::withMessages([
                    'nok_id' => 'এই Nok আপনার জন্য নয়।',
                ]);
            }

            if ($nok->status === 'expired' || ($nok->status === 'pending' && $nok->expires_at->isPast())) {
                $nok->forceFill(['status' => 'expired'])->save();

                throw ValidationException::withMessages([
                    'nok_id' => 'এই Nok এর মেয়াদ শেষ হয়ে গেছে।',
                ]);
            }

            if ($nok->status !== 'pending') {
                throw ValidationException::withMessages([
                    'nok_id' => 'এই Nok এর উপর ইতিমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
                ]);
            }

            // UNIQUE (job_post_id, worker_id) — job_interests টেবিলে ডুপ্লিকেট গার্ড
            $existingInterest = JobInterest::where('job_post_id', $nok->job_post_id)
                ->where('worker_id', $nok->worker_id)
                ->first();

            if ($existingInterest) {
                throw ValidationException::withMessages([
                    'nok_id' => 'এই Job এর জন্য ইতিমধ্যে একটি আবেদন বিদ্যমান আছে।',
                ]);
            }

            $nok->status = 'accepted';
            $nok->responded_at = now();
            $nok->save();

            $interest = JobInterest::create([
                'job_post_id'      => $nok->job_post_id,
                'worker_id'        => $nok->worker_id,
                'user_id'          => $worker->id,
                'interested_by_id' => null,
                'fee_reveal_id'    => null,
                'interest_note'    => null,
                'interest_source'  => 'agent_nok',
                'nok_id'           => $nok->id,
                'status'           => 'pending',
                'interested_at'    => now(),
            ]);

            return $interest;
        });

        // Transaction কমিট হওয়ার পরে fresh নক লোড করে notify (agent-কে জানানো)
        $nok = AgentNok::find($nokId);
        if ($nok) {
            $this->notifications->nokAccepted($nok);
        }

        return $interest;
    }

    /**
     * Worker কর্তৃক Nok Reject — কোনো refund নেই (Nok ফ্রি), agent কে notify করা হয়।
     */
    public function reject(int $nokId, User $worker): AgentNok
    {
        $nok = DB::transaction(function () use ($nokId, $worker) {

            $nok = AgentNok::lockForUpdate()->findOrFail($nokId);

            $workerProfile = Worker::where('worker_user_id', $worker->id)
                ->firstOrFail();

            if ($nok->worker_id !== $workerProfile->id) {
                throw ValidationException::withMessages([
                    'nok_id' => 'এই Nok আপনার জন্য নয়।',
                ]);
            }

            if ($nok->status !== 'pending') {
                throw ValidationException::withMessages([
                    'nok_id' => 'এই Nok এর উপর ইতিমধ্যে সিদ্ধান্ত নেওয়া হয়েছে।',
                ]);
            }

            $nok->status = 'rejected';
            $nok->responded_at = now();
            $nok->save();

            return $nok;
        });

        $this->notifications->nokRejected($nok);

        return $nok;
    }

    /**
     * Scheduler কমান্ড থেকে কল হয় — ৪৮ ঘণ্টা (settings.nok_expire_hours) পার হওয়া
     * pending Nok গুলোকে বাল্কে expired করে দেয়। প্রতিটির জন্য আলাদাভাবে
     * (agent + worker উভয়কে) notify করা হয়, তাই আগে ID গুলো তুলে নিয়ে
     * তারপর bulk update করা হচ্ছে।
     */
    public function expirePending(): int
    {
        $expiredNoks = AgentNok::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        if ($expiredNoks->isEmpty()) {
            return 0;
        }

        AgentNok::whereIn('id', $expiredNoks->pluck('id'))
            ->update(['status' => 'expired']);

        foreach ($expiredNoks as $nok) {
            $this->notifications->nokExpired($nok);
        }

        return $expiredNoks->count();
    }

    private function countLabel(array $workerIds): int
    {
        return count($workerIds);
    }
}