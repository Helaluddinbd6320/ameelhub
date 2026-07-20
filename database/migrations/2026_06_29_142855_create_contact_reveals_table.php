<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_reveals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('worker_id')->notNull()->constrained('workers')->cascadeOnDelete();
            $table->decimal('amount_charged', 4, 2)->notNull();
            $table->enum('phone_type', ['primary', 'whatsapp', 'saudi'])->notNull();
            $table->string('ip_address', 45)->notNull();
            $table->timestamp('revealed_at')->notNull()->useCurrent();

            $table->unique(['user_id', 'worker_id', 'phone_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_reveals');
    }
};