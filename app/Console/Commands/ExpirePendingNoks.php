<?php

namespace App\Console\Commands;

use App\Services\NokService;
use Illuminate\Console\Command;

class ExpirePendingNoks extends Command
{
    protected $signature = 'noks:expire-pending';

    protected $description = '৪৮ ঘণ্টার বেশি সময় ধরে pending থাকা Agent Nok গুলো auto-expire করে দেয়';

    public function handle(NokService $nokService): int
    {
        $count = $nokService->expirePending();

        $this->info("{$count} টি Nok expire করা হয়েছে।");

        return self::SUCCESS;
    }
}