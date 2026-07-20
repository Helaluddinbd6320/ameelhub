<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_uuid_to_agent_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_profiles', function (Blueprint $table) {
            // nullable + unique first — MySQL এ multiple NULL unique-এ সমস্যা করে না
            $table->uuid('uuid')->nullable()->unique()->after('id');
        });

        // বিদ্যমান row থাকলে (যেমন seeder agent) uuid backfill
        DB::table('agent_profiles')->whereNull('uuid')->orderBy('id')->each(function ($row) {
            DB::table('agent_profiles')
                ->where('id', $row->id)
                ->update(['uuid' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('agent_profiles', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};