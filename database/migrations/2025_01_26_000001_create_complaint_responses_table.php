<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained('complaints')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('response_type', [
                'comment',
                'status_update',
                'resolution',
                'escalation',
                'assignment',
                'follow_up'
            ])->default('comment');
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->json('metadata')->nullable(); // For storing additional data like old/new status
            $table->timestamps();

            // Indexes
            $table->index(['complaint_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['response_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_responses');
    }
};