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
            // Drop the old service_type column
            $table->dropColumn('service_type');

            // Add new columns
            $table->foreignId('service_id')->after('member_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->string('check_in_location')->nullable()->after('check_in_method');
            $table->foreignId('checked_in_by')->nullable()->after('check_in_location')
                  ->constrained('users')
                  ->nullOnDelete();
            $table->softDeletes();

            // Add indexes
            $table->index('check_in_method');
            $table->index('check_in_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['check_in_method']);
            $table->dropIndex(['check_in_time']);

            // Remove foreign key constraints
            $table->dropForeign(['service_id']);
            $table->dropForeign(['checked_in_by']);

            // Remove columns
            $table->dropColumn([
                'service_id',
                'check_in_location',
                'checked_in_by',
                'deleted_at'
            ]);

            // Restore the old service_type column
            $table->string('service_type')->after('member_id');
        });
    }
};