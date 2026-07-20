<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_deal_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_deal_id')->notNull()->constrained('job_deals')->cascadeOnDelete();
            $table->unsignedTinyInteger('milestone_number')->notNull();
            $table->string('title', 200)->notNull();
            $table->text('description')->nullable();
            $table->decimal('percentage', 5, 2)->notNull();
            $table->decimal('amount_sar', 8, 2)->notNull();
            $table->decimal('commission_sar', 8, 2)->notNull();
            $table->decimal('agent_receives_sar', 8, 2)->notNull();

            $table->enum('status', ['pending', 'worker_confirmed', 'agent_confirmed', 'admin_released', 'disputed'])
                  ->notNull()->default('pending');
            $table->timestamp('worker_confirmed_at')->nullable();
            $table->timestamp('agent_confirmed_at')->nullable();
            $table->timestamp('admin_released_at')->nullable();
            $table->foreignId('released_by_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('dispute_raised_by', ['worker', 'agent'])->nullable();
            $table->text('dispute_reason')->nullable();
            $table->timestamp('dispute_raised_at')->nullable();
            $table->enum('resolution', ['released', 'refunded', 'partial'])->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_deal_milestones');
    }
};