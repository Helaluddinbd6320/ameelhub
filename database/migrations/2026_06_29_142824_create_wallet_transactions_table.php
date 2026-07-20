<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->string('type', 50)->notNull();
            $table->decimal('amount', 10, 2)->unsigned()->notNull();
            $table->enum('direction', ['credit', 'debit'])->notNull();
            $table->string('balance_type', 30)->notNull();
            $table->decimal('available_before', 10, 2)->notNull();
            $table->decimal('available_after', 10, 2)->notNull();
            $table->decimal('held_before', 10, 2)->notNull();
            $table->decimal('held_after', 10, 2)->notNull();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description', 500)->notNull();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->notNull()->useCurrent();

            $table->index('user_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};