<?php

namespace App\Services;

use App\Models\AgentProfile;
use App\Models\JobPost;
use App\Models\Setting;
use App\Models\Worker;

class SeoService
{
    protected string $siteName;
    protected string $siteUrl;

    public function __construct()
    {
        $this->siteName = Setting::get('site_name', config('app.name', 'AmeelHub'));
        $this->siteUrl  = rtrim(Setting::get('site_url', config('app.url')), '/');
    }

    protected function base(): array
    {
        return [
            'title'       => $this->siteName,
            'description' => 'AmeelHub — বাংলাদেশি কর্মী ও সৌদি আরবের নিয়োগকর্তাদের মধ্যে নিরাপদ, escrow-ভিত্তিক নিয়োগ প্ল্যাটফর্ম।',
            'canonical'   => $this->siteUrl,
            'image'       => asset('images/og-default.jpg'),
            'type'        => 'website',
        ];
    }

    public function home(): array
    {
        return array_merge($this->base(), [
            'title'       => "{$this->siteName} — বিশ্বস্ত কর্মী নিয়োগ প্ল্যাটফর্ম",
            'description' => 'সৌদি আরবে কাজের জন্য যাচাইকৃত বাংলাদেশি কর্মী খুঁজুন অথবা কর্মী হিসেবে বিশ্বস্ত এজেন্টের মাধ্যমে চাকরি পান — Escrow সুরক্ষিত পেমেন্ট সহ।',
            'canonical'   => route('home'),
        ]);
    }

    public function workerList(): array
    {
        return array_merge($this->base(), [
            'title'       => "কর্মীদের তালিকা — {$this->siteName}",
            'description' => 'সৌদি আরবের জন্য যাচাইকৃত বাংলাদেশি কর্মীদের প্রোফাইল দেখুন — দক্ষতা, অভিজ্ঞতা ও প্রত্যাশিত বেতন অনুযায়ী।',
            'canonical'   => route('workers.index'),
        ]);
    }

    public function worker(Worker $worker): array
    {
        $name  = $worker->full_name_bn ?: $worker->full_name_en ?: 'কর্মী প্রোফাইল';
        $skill = $worker->skillCategory?->name_bn ?? $worker->skillCategory?->name_en;

        $descParts = array_filter([
            $skill ? "পেশা: {$skill}" : null,
            $worker->experience_years ? "অভিজ্ঞতা: {$worker->experience_years} বছর" : null,
            $worker->expected_salary_sar ? "প্রত্যাশিত বেতন: {$worker->expected_salary_sar} SAR" : null,
        ]);

        return array_merge($this->base(), [
            'title'       => "{$name} — {$this->siteName}",
            'description' => $descParts ? implode(' | ', $descParts) : "AmeelHub-এ {$name}-এর প্রোফাইল দেখুন।",
            'canonical'   => route('workers.show', $worker->uuid),
            'image'       => $worker->photo ? asset('storage/' . $worker->photo) : asset('images/og-default.jpg'),
            'type'        => 'profile',
        ]);
    }

    public function jobList(): array
    {
        return array_merge($this->base(), [
            'title'       => "চাকরির তালিকা — {$this->siteName}",
            'description' => 'সৌদি আরবে সক্রিয় চাকরির পোস্ট দেখুন — বেতন, আবাসন, খাবার ও অন্যান্য সুবিধা সহ।',
            'canonical'   => route('jobs.index'),
        ]);
    }

    public function job(JobPost $job): array
    {
        $descParts = array_filter([
            $job->employer_city ? "স্থান: {$job->employer_city}" : null,
            $job->salary_sar ? "বেতন: {$job->salary_sar} SAR" : null,
            $job->vacancies ? "শূন্যপদ: {$job->vacancies}" : null,
        ]);

        return array_merge($this->base(), [
            'title'       => "{$job->job_title} — {$this->siteName}",
            'description' => $descParts ? implode(' | ', $descParts) : $job->job_title,
            'canonical'   => route('jobs.show', $job->uuid),
        ]);
    }

    public function agentLeaderboard(): array
    {
        return array_merge($this->base(), [
            'title'       => "এজেন্ট লিডারবোর্ড — {$this->siteName}",
            'description' => 'AmeelHub-এর সবচেয়ে সফল যাচাইকৃত এজেন্টদের তালিকা দেখুন।',
            'canonical'   => route('agents.leaderboard'),
        ]);
    }

    public function agent(AgentProfile $agent): array
    {
        $name = $agent->agent_name_bn ?: $agent->agent_name_en ?: 'এজেন্ট প্রোফাইল';

        return array_merge($this->base(), [
            'title'       => "{$name} — {$this->siteName}",
            'description' => trim("{$name}" . ($agent->company_name ? " ({$agent->company_name})" : '')
                . " — সফল ডিল: {$agent->successful_deals}, নিয়োগকৃত কর্মী: {$agent->total_workers_placed}।"),
            'canonical'   => route('agents.show', $agent->uuid),
            'type'        => 'profile',
        ]);
    }
}