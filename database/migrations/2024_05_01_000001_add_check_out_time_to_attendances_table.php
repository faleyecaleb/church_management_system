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
        Schema::table('attendances', function (Blueprint $table) {
            // Check if the column doesn't exist before adding it
            if (!Schema::hasColumn('attendances', 'check_out_time')) {
                $table->dateTime('check_out_time')->nullable()->after('check_in_time');
                $table->foreignId('checked_out_by')->nullable()->after('checked_in_by')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'check_out_time')) {
                $table->dropForeign(['checked_out_by']);
                $table->dropColumn(['check_out_time', 'checked_out_by']);
            }
        });
    }
};