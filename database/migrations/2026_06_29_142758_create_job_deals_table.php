<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_deals', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->notNull()->unique();
            $table->foreignId('job_selection_id')->notNull()->constrained('job_selections')->cascadeOnDelete();
            $table->foreignId('job_post_id')->notNull()->constrained('job_posts');
            $table->foreignId('worker_id')->notNull()->constrained('workers');
            $table->foreignId('agent_id')->notNull()->constrained('users');
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('agent_fee_sar', 8, 2)->notNull();
            $table->decimal('chapai_commission_pct', 4, 2)->notNull()->default(8.00);  // GUARDED
            $table->decimal('chapai_commission_sar', 8, 2)->notNull();                 // GUARDED
            $table->decimal('agent_receives_sar', 8, 2)->notNull();                    // GUARDED

            $table->enum('status', ['confirmed', 'working', 'disputed', 'resolved', 'refunded', 'cancelled', 'completed'])
                  ->notNull()->default('confirmed');                                    // GUARDED
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('working_at')->nullable();
            $table->timestamp('completed_at')->nullable();                              // GUARDED
            $table->timestamp('cancelled_at')->nullable();
            $table->enum('dispute_raised_by', ['worker', 'agent'])->nullable();
            $table->text('dispute_reason')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_deals');
    }
};