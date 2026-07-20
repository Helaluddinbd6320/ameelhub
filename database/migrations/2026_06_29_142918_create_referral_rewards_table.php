<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('referee_id')->notNull()->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('reward_amount', 8, 2)->notNull();
            $table->enum('status', ['pending', 'paid'])->notNull()->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->notNull()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};