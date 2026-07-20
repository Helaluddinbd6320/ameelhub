<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->notNull()->unique();
            $table->foreignId('posted_by_id')->notNull()->constrained('users')->cascadeOnDelete();

            $table->string('job_title', 200)->notNull();
            $table->string('job_title_ar', 200)->nullable();
            $table->string('employer_name', 200)->notNull();
            $table->enum('employer_type', ['restaurant', 'hotel', 'factory', 'house', 'company', 'other'])->notNull();
            $table->string('employer_city', 100)->notNull();
            $table->string('employer_country', 100)->notNull()->default('Saudi Arabia');
            $table->foreignId('skill_category_id')->notNull()->constrained('skill_categories');
            $table->string('skill_sub_details', 200)->nullable();

            $table->unsignedTinyInteger('vacancies')->notNull();
            $table->decimal('salary_sar', 8, 2)->notNull();
            $table->boolean('accommodation')->notNull()->default(false);
            $table->boolean('food_included')->notNull()->default(false);
            $table->boolean('transport_provided')->notNull()->default(false);
            $table->unsignedTinyInteger('contract_months')->nullable();
            $table->unsignedTinyInteger('working_hours')->nullable();
            $table->string('weekly_off', 50)->nullable();
            $table->boolean('overtime_available')->notNull()->default(false);
            $table->text('description')->nullable();
            $table->text('requirements')->nullable();

            $table->text('agent_fee_sar')->notNull();              // encrypted
            $table->decimal('fee_reveal_cost', 4, 2)->notNull()->default(0.50);

            // Lifecycle (GUARDED fields noted)
            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'closed', 'filled'])
                  ->notNull()->default('draft');
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedTinyInteger('filled_count')->notNull()->default(0);
            $table->unsignedInteger('view_count')->notNull()->default(0);
            $table->string('close_reason', 500)->nullable();
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};