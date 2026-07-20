<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->notNull()->unique();
            $table->foreignId('submitted_by_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('worker_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Section 1: Personal
            $table->string('full_name_bn', 100)->nullable();
            $table->string('full_name_en', 100)->nullable();
            $table->string('father_name_bn', 100)->nullable();
            $table->string('father_name_en', 100)->nullable();
            $table->string('mother_name_bn', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('religion', ['islam', 'hindu', 'christian', 'buddhist', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality', 50)->notNull()->default('Bangladeshi');
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->unsignedSmallInteger('height_cm')->nullable();
            $table->unsignedSmallInteger('weight_kg')->nullable();
            $table->string('photo', 500)->nullable();

            // Section 2: Bangladesh Address
            $table->string('permanent_division', 50)->nullable();
            $table->string('permanent_district', 50)->nullable();
            $table->string('permanent_upazila', 50)->nullable();
            $table->string('permanent_village', 100)->nullable();
            $table->string('permanent_post_code', 10)->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_relation', 50)->nullable();
            $table->text('emergency_contact_phone')->nullable();   // encrypted

            // Section 3: Passport + NID
            $table->text('passport_number')->nullable();           // encrypted
            $table->date('passport_issue_date')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('passport_issue_place', 100)->nullable();
            $table->text('nid_number')->nullable();                // encrypted

            // Section 4: Iqama
            $table->text('iqama_number')->nullable();              // encrypted
            $table->date('iqama_expiry')->nullable();
            $table->string('iqama_profession_ar', 100)->nullable();
            $table->string('iqama_profession_bn', 100)->nullable();
            $table->string('current_sponsor_name', 200)->nullable();
            $table->string('current_sponsor_cr', 50)->nullable();

            // Section 5: Contact (HIDDEN — 5 SAR paywall)
            $table->text('phone_primary')->nullable();             // encrypted
            $table->text('phone_whatsapp')->nullable();            // encrypted
            $table->text('phone_saudi')->nullable();               // encrypted
            $table->text('email_personal')->nullable();            // encrypted

            // Section 6: Skills
            $table->foreignId('skill_category_id')->nullable()->constrained('skill_categories')->nullOnDelete();
            $table->string('skill_sub_details', 300)->nullable();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->unsignedTinyInteger('experience_saudi_years')->nullable();
            $table->string('previous_companies', 500)->nullable();
            $table->enum('education_level', ['none', 'primary', 'secondary', 'hsc', 'degree'])->nullable();
            $table->string('education_details', 200)->nullable();
            $table->enum('arabic_level', ['none', 'basic', 'intermediate', 'fluent'])->nullable();
            $table->enum('english_level', ['none', 'basic', 'intermediate', 'fluent'])->nullable();
            $table->boolean('driving_license')->notNull()->default(false);
            $table->string('driving_license_type', 50)->nullable();
            $table->string('computer_skills', 200)->nullable();
            $table->string('other_skills', 300)->nullable();
            $table->string('skill_video_youtube', 255)->nullable();

            // Section 7: Current Status in Saudi
            $table->boolean('is_in_saudi')->notNull()->default(false);
            $table->string('present_location_city', 100)->nullable();
            $table->string('present_location_country', 100)->nullable()->default('Saudi Arabia');
            $table->enum('visa_status', ['visit', 'iqama', 'free_exit', 'final_exit', 'new_visa', 'not_in_saudi'])->nullable();
            $table->boolean('transfer_possible')->notNull()->default(false);
            $table->date('available_from')->nullable();
            $table->decimal('expected_salary_sar', 8, 2)->nullable();

            // Section 8: Medical
            $table->boolean('medical_fit')->notNull()->default(true);
            $table->string('medical_notes', 500)->nullable();

            // Section 9: Admin fields (GUARDED)
            $table->enum('status', ['draft', 'pending', 'active', 'inactive', 'hired', 'featured', 'rejected'])
                  ->notNull()->default('draft');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_verified')->notNull()->default(false);
            $table->boolean('is_featured')->notNull()->default(false);
            $table->date('featured_until')->nullable();
            $table->text('cv_notes')->nullable();
            $table->boolean('approval_fee_charged')->notNull()->default(false);
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('view_count')->notNull()->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('iqama_expiry');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workers');
    }
};