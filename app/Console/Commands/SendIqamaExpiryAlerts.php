<?php

namespace App\Console\Commands;

use App\Models\Worker;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendIqamaExpiryAlerts extends Command
{
    protected $signature = 'iqama:expiry-alert';

    protected $description = 'Send a daily digest to Admin listing workers whose Iqama expires within 30 days';

    public function handle(NotificationService $notifications): int
    {
        // Only workers currently in Saudi with an active/featured CV and
        // a set iqama_expiry date are relevant (Section 4: "Iqama — Saudi
        // workers only"). iqama_expiry is a plain DATE cast (not encrypted),
        // so direct DB filtering is safe here.
        $expiringWorkers = Worker::query()
            ->whereIn('status', ['active', 'featured'])
            ->where('is_in_saudi', true)
            ->whereNotNull('iqama_expiry')
            ->whereBetween('iqama_expiry', [
                now()->startOfDay(),
                now()->addDays(30)->endOfDay(),
            ])
            ->orderBy('iqama_expiry')
            ->get();

        $notifications->iqamaExpiryDigest($expiringWorkers);

        $this->info("Flagged {$expiringWorkers->count()} worker(s) with Iqama expiring within 30 days.");

        return self::SUCCESS;
    }
}