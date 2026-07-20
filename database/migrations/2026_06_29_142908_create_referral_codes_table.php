<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNull()->unique()->constrained('users')->cascadeOnDelete();
            $table->string('code', 20)->notNull()->unique();
            $table->unsignedInteger('used_count')->notNull()->default(0);
            $table->timestamp('created_at')->notNull()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_codes');
    }
};