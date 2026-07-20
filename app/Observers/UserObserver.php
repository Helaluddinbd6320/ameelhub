<?php

namespace App\Observers;

use App\Models\AgentProfile;
use App\Models\User;
use App\Models\Worker;
use App\Services\ReferralService;
use Illuminate\Support\Str;

class UserObserver
{
    public function __construct(protected ReferralService $referralService)
    {
    }

    public function created(User $user): void
    {
        if ($user->role === 'worker') {
            // Guard: when a Worker CV is submitted by an Agent/Admin,
            // WorkerAccountService creates this exact User row
            // (account_source = 'agent_created') to link back to that
            // *already-existing* CV via worker_user_id. Without this
            // check, this observer would fire again on that same
            // creation and insert a second, blank "ghost" Worker CV.
            //
            // Only self-registered signups (Breeze/Google/Facebook,
            // account_source = 'self_registered') get a fresh blank
            // scaffold CV here.
            if ($user->account_source !== 'agent_created') {
                // SECURITY FIX (Step 10.7 audit): 'status' is now guarded
                // on the Worker model, so a plain create() would silently
                // drop it, leaving status = NULL instead of 'draft'.
                // forceCreate() bypasses mass-assignment protection for
                // this trusted, system-controlled insert.
                Worker::forceCreate([
                    'uuid'            => (string) Str::uuid(),
                    'submitted_by_id' => $user->id,
                    'worker_user_id'  => $user->id,
                    'status'          => 'draft',
                    'nationality'     => 'Bangladeshi',
                ]);
            }
        }

        if ($user->role === 'agent') {
            // SECURITY FIX (Step 10.7 audit): 'user_id' and 'is_verified'
            // are now guarded on the AgentProfile model. firstOrCreate()
            // calls create() internally, so both fields would silently
            // be dropped — the profile would be created with a NULL
            // user_id (or fail on the NOT NULL/unique constraint) and
            // is_verified would fall back to its DB default instead of
            // the explicit false we want here. Laravel has no "forced"
            // variant of firstOrCreate(), so we do the existence check
            // manually and use forceCreate() for the insert.
            if (! AgentProfile::where('user_id', $user->id)->exists()) {
                AgentProfile::forceCreate([
                    'user_id'     => $user->id,
                    'is_verified' => false,
                    'country'     => 'Saudi Arabia',
                ]);
            }
        }

        // সব রোলের জন্য রেফারেল কোড তৈরি (worker + agent উভয়ই) —
        // ReferralService::generateCode() ইউনিক কোড গ্যারান্টি দেয়।
        $this->referralService->generateCode($user);
    }
}