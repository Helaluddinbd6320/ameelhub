<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'self_registered' → user signed up themselves (Breeze/Google/Facebook)
            // 'agent_created'   → account was auto-created because an Agent
            //                     submitted a Worker CV on their behalf
            //                     (see WorkerAccountService / WorkerObserver)
            $table->enum('account_source', ['self_registered', 'agent_created'])
                ->default('self_registered')
                ->after('role');

            // NULL until the worker verifies their phone/email and sets
            // their own password — i.e. "claims" an agent-created account.
            // Claim flow itself is a future step (Phase 9/10 TODO).
            $table->timestamp('claimed_at')->nullable()->after('account_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_source', 'claimed_at']);
        });
    }
};