<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL doesn't support adding an enum value via Schema::table()/
     * change() cleanly without doctrine/dbal quirks, so we use a raw
     * ALTER TABLE statement here. This keeps the original
     * create_job_posts_table migration untouched (already run).
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE job_posts
            MODIFY COLUMN status ENUM('draft', 'pending', 'active', 'paused', 'closed', 'filled', 'rejected')
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        // Revert: any rows with 'rejected' must be migrated first,
        // otherwise this rollback will fail with a data truncation error.
        DB::statement("
            UPDATE job_posts SET status = 'closed' WHERE status = 'rejected'
        ");

        DB::statement("
            ALTER TABLE job_posts
            MODIFY COLUMN status ENUM('draft', 'pending', 'active', 'paused', 'closed', 'filled')
            NOT NULL DEFAULT 'draft'
        ");
    }
};