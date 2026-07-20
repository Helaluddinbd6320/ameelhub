<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_fee_reveals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_post_id')->notNull()->constrained('job_posts')->cascadeOnDelete();
            $table->decimal('amount_charged', 4, 2)->notNull();
            $table->string('ip_address', 45)->notNull();
            $table->timestamp('revealed_at')->notNull()->useCurrent();

            $table->unique(['user_id', 'job_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_fee_reveals');
    }
};