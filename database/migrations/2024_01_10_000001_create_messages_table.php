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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');  // sms, prayer, internal
            $table->string('subject')->nullable();
            $table->text('content');
            $table->string('description')->nullable();
            $table->string('category')->nullable();
            $table->json('variables')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'category']);
            $table->index('is_active');
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('type');  // sms, prayer, internal
            $table->foreignId('sender_id')->constrained('users');
            $table->string('recipient_type');  // individual, group
            $table->unsignedBigInteger('recipient_id');
            $table->string('subject')->nullable();
            $table->text('content');
            $table->foreignId('template_id')->nullable()->constrained('message_templates');
            $table->string('status')->default('pending');  // pending, sent, delivered, failed, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('scheduled_at');
        });

        Schema::create('message_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('static');  // static, dynamic, smart
            $table->json('criteria')->nullable();  // For dynamic groups
            $table->foreignId('created_by')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });

        Schema::create('message_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('active');  // active, inactive, blocked
            $table->timestamps();

            $table->unique(['message_group_id', 'user_id']);
            $table->index('status');
        });

        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->string('type');  // file, image, document
            $table->string('name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
        Schema::dropIfExists('message_group_members');
        Schema::dropIfExists('message_groups');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('messages');
    }
};