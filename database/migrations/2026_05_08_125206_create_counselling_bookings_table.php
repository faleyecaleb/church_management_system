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
        Schema::create('counselling_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('church_id')->nullable()->constrained()->cascadeOnDelete();
            
            $table->date('requested_date');
            $table->time('requested_time');
            
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('admin_notes')->nullable()->comment('Notes from the PA or Pastor');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counselling_bookings');
    }
};
