<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispute_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->notNull()->constrained('job_deal_milestones')->cascadeOnDelete();
            $table->foreignId('uploaded_by_id')->notNull()->constrained('users')->cascadeOnDelete();
            $table->enum('uploaded_by_role', ['worker', 'agent'])->notNull();
            $table->string('file_path', 500)->notNull();
            $table->enum('file_type', ['image', 'pdf', 'screenshot'])->notNull();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->notNull()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_evidences');
    }
};