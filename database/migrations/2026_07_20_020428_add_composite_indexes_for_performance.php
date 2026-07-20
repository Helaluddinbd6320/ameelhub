<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Step 10.8c — Performance: public listing pages একসাথে একাধিক column
     * (status + skill_category_id / visa_status / is_in_saudi) filter করে,
     * কিন্তু আগে শুধু single-column index ছিল। Composite index যোগ করে
     * MySQL কে একটা index scan-এই বেশিরভাগ ফিল্টার resolve করতে সাহায্য করা হলো।
     */
    public function up(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            // WorkerList.php: status + skill_category_id একসাথে filter হয়
            $table->index(['status', 'skill_category_id'], 'workers_status_skill_category_index');

            // visa_status আগে filter হিসেবে ব্যবহৃত হলেও কোনো index ছিল না
            $table->index(['status', 'visa_status'], 'workers_status_visa_status_index');

            // is_in_saudi আগে filter হিসেবে ব্যবহৃত হলেও কোনো index ছিল না
            $table->index(['status', 'is_in_saudi'], 'workers_status_is_in_saudi_index');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            // JobList.php + Homepage.php: status + expires_at একসাথে filter হয়
            $table->index(['status', 'expires_at'], 'job_posts_status_expires_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropIndex('workers_status_skill_category_index');
            $table->dropIndex('workers_status_visa_status_index');
            $table->dropIndex('workers_status_is_in_saudi_index');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex('job_posts_status_expires_at_index');
        });
    }
};