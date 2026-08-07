<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();

            // cash বাদ দেওয়া হয়েছে — cash এ কোনো account লাগে না, সরাসরি অফিসে জমা
            $table->enum('payment_method', ['bank', 'bkash', 'nagad', 'stcpay']);

            $table->string('account_label', 100);      // Admin-এর নিজের চেনার জন্য: "Chapai Main bKash"
            $table->string('account_holder_name', 150);
            $table->string('account_number', 100);      // bkash/nagad/stcpay নম্বর অথবা bank account no.
            $table->string('bank_name', 100)->nullable();
            $table->string('branch_name', 100)->nullable();
            $table->string('routing_or_iban', 100)->nullable();
            $table->text('instructions_bn')->nullable(); // যেমন: "Send Money অপশনে পাঠান, Payment না"

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['payment_method', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};