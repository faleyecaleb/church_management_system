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
        // Create pivot table for member departments
        Schema::create('member_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('department');
            $table->timestamps();
            
            $table->unique(['member_id', 'department']);
        });

        // Migrate existing department data
        if (Schema::hasColumn('members', 'department')) {
            // Move existing department data to the new table
            DB::statement("
                INSERT INTO member_departments (member_id, department, created_at, updated_at)
                SELECT id, department, created_at, updated_at 
                FROM members 
                WHERE department IS NOT NULL AND department != ''
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_departments');
    }
};