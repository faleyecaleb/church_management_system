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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // birthday, anniversary, milestone, custom, followup
            $table->string('title');
            $table->text('message');
            $table->morphs('recipient'); // Polymorphic relationship for different recipient types
            $table->json('data')->nullable(); // Additional data specific to notification type
            $table->timestamp('read_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status'); // pending, scheduled, sent, failed
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better query performance
            $table->index(['type', 'status']);
            $table->index('scheduled_at');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};