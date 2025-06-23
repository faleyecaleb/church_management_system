<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->date('baptism_date')->nullable();
            $table->string('membership_status')->default('active');
            $table->string('profile_photo')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->json('emergency_contacts')->nullable();
            $table->json('custom_fields')->nullable();
            $table->enum('department', ['Media', 'Choir', 'Ushers', 'Dance', 'Prayer', 'Lost but Found', 'Drama', 'Sanctuary'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};