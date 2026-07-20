<?php

namespace App\Console\Commands;

use App\Services\JobLifecycleService;
use Illuminate\Console\Command;

class AlertStaleJobs extends Command
{
    protected $signature = 'jobs:alert-stale';

    protected $description = 'Alert agents whose active jobs have no interest after N days';

    public function handle(JobLifecycleService $service): int
    {
        $count = $service->alertStale();

        $this->info("Flagged {$count} stale job post(s) for alert.");

        return self::SUCCESS;
    }
}