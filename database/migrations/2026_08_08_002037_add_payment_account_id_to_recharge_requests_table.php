<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recharge_requests', function (Blueprint $table) {
            $table->foreignId('payment_account_id')
                ->nullable()
                ->after('proof_file')
                ->constrained('payment_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recharge_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_account_id');
        });
    }
};