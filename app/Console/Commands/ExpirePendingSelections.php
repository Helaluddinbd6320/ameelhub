<?php

namespace App\Console\Commands;

use App\Services\JobSelectionService;
use Illuminate\Console\Command;

class ExpirePendingSelections extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'selections:expire-pending';

    /**
     * The console command description.
     */
    protected $description = 'মেয়াদোত্তীর্ণ Pending Job Selections (worker_response=pending, expires_at পার হয়েছে) auto-expire করে, সংশ্লিষ্ট Interest রিসেট করে, এবং Agent + Worker কে notify করে।';

    public function handle(JobSelectionService $service): int
    {
        $count = $service->expirePending();

        $this->info("Expired {$count} pending job selection(s).");

        return self::SUCCESS;
    }
}