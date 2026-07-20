<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNull()->unique()->constrained('users')->cascadeOnDelete();

            $table->string('agent_name_bn', 100)->nullable();
            $table->string('agent_name_en', 100)->nullable();
            $table->string('company_name', 200)->nullable();
            $table->enum('company_type', ['individual', 'registered_company', 'recruitment_agency'])->nullable();
            $table->string('office_address', 500)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Saudi Arabia');
            $table->text('phone_office')->nullable();        // encrypted
            $table->text('whatsapp_number')->nullable();     // encrypted
            $table->unsignedTinyInteger('years_in_business')->nullable();

            // Verification documents (private disk — ULID filenames)
            $table->string('passport_copy', 500)->nullable();
            $table->string('nid_copy', 500)->nullable();
            $table->string('agency_license', 500)->nullable();
            $table->string('company_cr_copy', 500)->nullable();

            // Verification status (GUARDED)
            $table->boolean('is_verified')->notNull()->default(false);
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            // Stats (GUARDED — updated by system)
            $table->unsignedInteger('total_cvs_submitted')->notNull()->default(0);
            $table->unsignedInteger('total_jobs_posted')->notNull()->default(0);
            $table->unsignedInteger('total_deals')->notNull()->default(0);
            $table->unsignedInteger('successful_deals')->notNull()->default(0);
            $table->unsignedInteger('total_workers_placed')->notNull()->default(0);
            $table->timestamp('last_deal_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_profiles');
    }
};