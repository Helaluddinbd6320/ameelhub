<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->notNull()->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('job_interest_id')->notNull()->constrained('job_interests')->cascadeOnDelete();
            $table->foreignId('worker_id')->notNull()->constrained('workers')->cascadeOnDelete();
            $table->foreignId('agent_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->decimal('agent_fee_sar', 8, 2)->notNull();
            $table->timestamp('notification_sent_at')->nullable();
            $table->enum('worker_response', ['pending', 'accepted', 'rejected', 'expired'])
                  ->notNull()->default('pending');
            $table->timestamp('worker_responded_at')->nullable();
            $table->timestamp('expires_at')->notNull()->default(DB::raw('CURRENT_TIMESTAMP'));

            $table->index('worker_response');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_selections');
    }
};