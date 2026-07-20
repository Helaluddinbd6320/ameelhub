<?php

namespace App\Console\Commands;

use App\Services\JobLifecycleService;
use Illuminate\Console\Command;

class AutoCloseExpiredJobs extends Command
{
    protected $signature = 'jobs:auto-close-expired';

    protected $description = 'Auto-close job posts whose expires_at date has passed';

    public function handle(JobLifecycleService $service): int
    {
        $count = $service->autoCloseExpired();

        $this->info("Auto-closed (expired) {$count} job post(s).");

        return self::SUCCESS;
    }
}