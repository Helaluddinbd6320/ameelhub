<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->notNull()->constrained('job_posts')->cascadeOnDelete();
            $table->foreignId('worker_id')->notNull()->constrained('workers')->cascadeOnDelete();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('interested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('fee_reveal_id')->nullable()->constrained('job_fee_reveals')->nullOnDelete();
            $table->string('interest_note', 500)->nullable();
            $table->enum('interest_source', ['worker_self', 'agent_nok', 'agent_select'])
                  ->notNull()->default('worker_self');
            $table->foreignId('nok_id')->nullable();    // FK added after agent_noks
            $table->enum('status', ['pending', 'selected', 'rejected', 'hired'])
                  ->notNull()->default('pending');
            $table->timestamp('interested_at')->notNull()->useCurrent();

            $table->unique(['job_post_id', 'worker_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_interests');
    }
};