<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, let's standardize the attendance table structure
        Schema::table('attendances', function (Blueprint $table) {
            // Add attendance_date column if it doesn't exist
            if (!Schema::hasColumn('attendances', 'attendance_date')) {
                $table->date('attendance_date')->nullable()->after('service_id');
            }
            
            // Ensure all necessary columns exist with proper types
            if (!Schema::hasColumn('attendances', 'status')) {
                $table->enum('status', ['present', 'absent', 'late'])->default('present')->after('attendance_date');
            }
            
            // Add proper foreign key constraints if they don't exist
            if (!$this->foreignKeyExists('attendances', 'attendances_member_id_foreign')) {
                $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            }
            
            if (!$this->foreignKeyExists('attendances', 'attendances_service_id_foreign')) {
                $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            }
            
            if (!$this->foreignKeyExists('attendances', 'attendances_checked_in_by_foreign')) {
                $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('set null');
            }
        });

        // Populate attendance_date from check_in_time for existing records
        DB::statement("
            UPDATE attendances 
            SET attendance_date = DATE(check_in_time) 
            WHERE attendance_date IS NULL AND check_in_time IS NOT NULL
        ");

        // Make attendance_date not nullable after populating data
        Schema::table('attendances', function (Blueprint $table) {
            $table->date('attendance_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Remove foreign keys
            if ($this->foreignKeyExists('attendances', 'attendances_member_id_foreign')) {
                $table->dropForeign('attendances_member_id_foreign');
            }
            if ($this->foreignKeyExists('attendances', 'attendances_service_id_foreign')) {
                $table->dropForeign('attendances_service_id_foreign');
            }
            if ($this->foreignKeyExists('attendances', 'attendances_checked_in_by_foreign')) {
                $table->dropForeign('attendances_checked_in_by_foreign');
            }
            
            // Remove attendance_date column
            if (Schema::hasColumn('attendances', 'attendance_date')) {
                $table->dropColumn('attendance_date');
            }
            
            if (Schema::hasColumn('attendances', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    /**
     * Check if foreign key exists
     */
    private function foreignKeyExists($table, $name): bool
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND CONSTRAINT_NAME = ?
        ", [$table, $name]);
        
        return count($foreignKeys) > 0;
    }
};