<?php

namespace App\Observers;

use App\Models\Worker;
use App\Services\WorkerAccountService;

class WorkerObserver
{
    public function __construct(
        protected WorkerAccountService $accounts
    ) {}

    /**
     * Fires after a Worker CV row is inserted.
     *
     * Self-registered workers (Step 2.2 — UserObserver@created) already
     * pass worker_user_id at creation time, so this is a no-op for them.
     *
     * Agent-submitted CVs (Agent Panel → MyWorkers → Create) leave
     * worker_user_id null, so this creates the linked placeholder account.
     */
    public function created(Worker $worker): void
    {
        if (! $worker->worker_user_id) {
            $this->accounts->ensureUserAccount($worker);
        }
    }
}