<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->notNull()->unique();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->notNull();
            $table->enum('payment_method', ['bank', 'bkash', 'nagad', 'stcpay', 'cash'])->notNull();
            $table->text('account_details')->notNull();    // encrypted
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])
                  ->notNull()->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};