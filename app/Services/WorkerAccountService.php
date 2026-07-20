<?php

namespace App\Services;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Support\Str;

class WorkerAccountService
{
    /**
     * Ensure the given Worker CV has a login-capable User account linked
     * via worker_user_id, creating one if the Worker themselves has never
     * registered (i.e. the CV was submitted entirely by an Agent).
     *
     * The auto-created account:
     *   - has a random, unknown-to-anyone password (cannot be logged into)
     *   - uses a placeholder email so it never collides with a real signup
     *   - is marked account_source = 'agent_created', claimed_at = null
     *
     * When the Worker later verifies their phone/email and sets their own
     * password, that "claim" flow (Phase 9/10 TODO) should update this same
     * User row rather than creating a new one — look up by worker_user_id.
     */
    public function ensureUserAccount(Worker $worker): User
    {
        if ($worker->worker_user_id) {
            return User::findOrFail($worker->worker_user_id);
        }

        $displayName = $worker->full_name_bn ?: ($worker->full_name_en ?: 'Worker');

        $user = User::create([
            'name'           => $displayName,
            'email'          => 'worker-' . Str::uuid() . '@ameelhub.placeholder',
            'password'       => Str::random(40),
            'role'           => 'worker',
            'account_source' => 'agent_created',
        ]);

        $worker->forceFill(['worker_user_id' => $user->id])->save();

        return $user;
    }
}