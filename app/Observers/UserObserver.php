<?php

namespace App\Observers;

use App\Models\AgentProfile;
use App\Models\User;
use App\Models\Worker;
use App\Services\ReferralService;
use Illuminate\Support\Str;

class UserObserver
{
    /**
     * Valid Spatie role names that map 1:1 with the plain `users.role`
     * column values used throughout the app.
     */
    protected const ASSIGNABLE_ROLES = ['super_admin', 'admin', 'staff', 'agent', 'worker'];

    public function __construct(protected ReferralService $referralService)
    {
    }

    public function created(User $user): void
    {
        // Spatie Role Assignment
        if (in_array($user->role, self::ASSIGNABLE_ROLES, true)) {
            $user->assignRole($user->role);
        }

        if ($user->role === 'worker') {
            // Guard: Agent-created worker accounts rely on WorkerAccountService to link existing CVs.
            // Self-registered workers (Breeze, Social Auth, etc.) explicitly get a scaffold blank CV.
            if ($user->account_source !== 'agent_created') {
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
            if (! AgentProfile::where('user_id', $user->id)->exists()) {
                AgentProfile::forceCreate([
                    'user_id'     => $user->id,
                    'is_verified' => false,
                    'country'     => 'Saudi Arabia',
                ]);
            }
        }

        // Generate referral code for user
        $this->referralService->generateCode($user);
    }
}