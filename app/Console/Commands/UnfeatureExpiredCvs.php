<?php

namespace App\Console\Commands;

use App\Models\Worker;
use Illuminate\Console\Command;

class UnfeatureExpiredCvs extends Command
{
    protected $signature = 'workers:unfeature-expired';

    protected $description = 'Featured মেয়াদ শেষ হওয়া Worker CV গুলোকে আবার Active status এ ফিরিয়ে আনে';

    public function handle(): int
    {
        $expired = Worker::where('is_featured', true)
            ->whereNotNull('featured_until')
            ->where('featured_until', '<', now())
            ->get();

        foreach ($expired as $worker) {
            $worker->forceFill([
                'is_featured'    => false,
                'status'         => 'active',
                'featured_until' => null,
            ])->save();
        }

        $this->info("{$expired->count()} টি Featured CV এর মেয়াদ শেষ হওয়ায় Active করা হয়েছে।");

        return self::SUCCESS;
    }
}