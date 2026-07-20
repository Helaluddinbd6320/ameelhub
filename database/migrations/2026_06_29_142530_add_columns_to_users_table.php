<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 100)->nullable()->index()->after('remember_token');
            $table->string('facebook_id', 100)->nullable()->index()->after('google_id');
            $table->string('avatar', 500)->nullable()->after('facebook_id');
            $table->string('role', 20)->notNull()->default('worker')->after('avatar');
            $table->decimal('available_balance', 10, 2)->notNull()->default(0.00)->after('role');
            $table->decimal('held_balance', 10, 2)->notNull()->default(0.00)->after('available_balance');
            $table->foreignId('referred_by_id')->nullable()->after('held_balance')
                  ->constrained('users')->nullOnDelete();
        });

        // DB-level constraints
        DB::statement('ALTER TABLE users ADD CONSTRAINT chk_avail CHECK (available_balance >= 0)');
        DB::statement('ALTER TABLE users ADD CONSTRAINT chk_held CHECK (held_balance >= 0)');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_id']);
            $table->dropColumn([
                'google_id', 'facebook_id', 'avatar', 'role',
                'available_balance', 'held_balance', 'referred_by_id',
            ]);
        });
    }
};