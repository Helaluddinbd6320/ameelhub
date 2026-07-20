<?php

namespace App\Console\Commands;

use App\Services\JobLifecycleService;
use Illuminate\Console\Command;

class AutoCloseFilledJobs extends Command
{
    protected $signature = 'jobs:auto-close-filled';

    protected $description = 'Auto-close job posts where filled_count >= vacancies';

    public function handle(JobLifecycleService $service): int
    {
        $count = $service->autoCloseFilled();

        $this->info("Auto-closed (filled) {$count} job post(s).");

        return self::SUCCESS;
    }
}