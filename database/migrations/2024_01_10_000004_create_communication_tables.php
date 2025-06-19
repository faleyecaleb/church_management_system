<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SMS Messages table
        Schema::create('sms_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('recipient_group')->nullable(); // all, members, visitors, custom
            $table->json('recipient_ids')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, sent, failed
            $table->integer('total_recipients')->default(0);
            $table->integer('successful_sends')->default(0);
            $table->integer('failed_sends')->default(0);
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });

        // SMS Templates table
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->string('category');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Prayer Requests table
        Schema::create('prayer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_private')->default(false);
            $table->string('status')->default('active'); // active, completed, archived
            $table->integer('prayer_count')->default(0);
            $table->dateTime('last_prayed_at')->nullable();
            $table->timestamps();
        });

        // Internal Messages table
        Schema::create('internal_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('members')->onDelete('cascade');
            $table->string('recipient_type'); // individual, group
            $table->json('recipient_ids');
            $table->string('subject');
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('sms_templates');
        Schema::dropIfExists('prayer_requests');
        Schema::dropIfExists('internal_messages');
    }
};