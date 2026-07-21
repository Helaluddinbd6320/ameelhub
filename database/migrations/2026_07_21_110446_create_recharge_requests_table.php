<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recharge_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->notNull()->unique();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2)->notNull();
            $table->enum('payment_method', ['bank', 'bkash', 'nagad', 'stcpay', 'cash'])->notNull();
            // User যে reference/transaction ID লিখেছে (bKash TrxID, ব্যাংক স্লিপ নম্বর ইত্যাদি)
            $table->string('reference_number', 100)->nullable();
            // Proof screenshot/PDF — private disk এ, ULID filename দিয়ে সংরক্ষিত
            $table->string('proof_file', 500)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->notNull()->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recharge_requests');
    }
};