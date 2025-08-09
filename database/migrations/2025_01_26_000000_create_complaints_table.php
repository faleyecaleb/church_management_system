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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->string('complainant_name')->nullable();
            $table->string('complainant_email')->nullable();
            $table->string('complainant_phone')->nullable();
            $table->string('department')->nullable();
            $table->enum('category', [
                'service_quality',
                'facility',
                'staff_behavior',
                'financial',
                'pastoral_care',
                'communication',
                'event_management',
                'other'
            ])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', [
                'open',
                'in_progress',
                'pending_review',
                'resolved',
                'closed',
                'escalated'
            ])->default('open');
            $table->string('subject');
            $table->text('description');
            $table->json('evidence_files')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('escalated_at')->nullable();
            $table->foreignId('escalated_to')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->integer('satisfaction_rating')->nullable()->comment('1-5 rating');
            $table->text('satisfaction_feedback')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['status', 'priority']);
            $table->index(['member_id', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['department', 'status']);
            $table->index(['follow_up_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};