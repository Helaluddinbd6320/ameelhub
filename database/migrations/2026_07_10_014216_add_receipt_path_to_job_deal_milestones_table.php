<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_deal_milestones', function (Blueprint $table) {
            $table->string('receipt_path', 500)->nullable()->after('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_deal_milestones', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
    }
};