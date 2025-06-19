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
        Schema::create('membership_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('status');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->constrained('users');
            $table->boolean('class_completed')->default(false);
            $table->string('transfer_church')->nullable();
            $table->datetime('transfer_date')->nullable();
            $table->datetime('renewal_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('renewal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_statuses');
    }
};