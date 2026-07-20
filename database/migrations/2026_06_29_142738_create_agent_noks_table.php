<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_noks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->notNull()->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('agent_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('worker_id')->notNull()->constrained('workers')->cascadeOnDelete();
            $table->foreignId('worker_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nok_message', 500)->nullable();
            $table->enum('route_source', ['route_a', 'route_b'])->notNull()->default('route_a');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])
                  ->notNull()->default('pending');
            $table->timestamp('sent_at')->notNull()->useCurrent();
            $table->timestamp('responded_at')->nullable();
$table->timestamp('expires_at')->notNull()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unique(['job_post_id', 'agent_id', 'worker_id']);
            $table->index('status');
            $table->index('expires_at');
        });

        // Now add the FK for nok_id in job_interests
        Schema::table('job_interests', function (Blueprint $table) {
            $table->foreign('nok_id')->references('id')->on('agent_noks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_interests', function (Blueprint $table) {
            $table->dropForeign(['nok_id']);
        });
        Schema::dropIfExists('agent_noks');
    }
};