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
        Schema::table('prayer_requests', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('prayer_requests', 'requestor_id')) {
                $table->foreignId('requestor_id')->nullable()->after('member_id')->constrained('users')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('prayer_requests', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('is_private');
            }
            
            if (!Schema::hasColumn('prayer_requests', 'prayer_target')) {
                $table->integer('prayer_target')->nullable()->after('prayer_count');
            }
            
            if (!Schema::hasColumn('prayer_requests', 'prayer_frequency')) {
                $table->integer('prayer_frequency')->nullable()->after('prayer_target')->comment('Days between prayers');
            }
            
            if (!Schema::hasColumn('prayer_requests', 'end_date')) {
                $table->date('end_date')->nullable()->after('prayer_frequency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_requests', function (Blueprint $table) {
            $table->dropColumn([
                'requestor_id',
                'is_public', 
                'prayer_target',
                'prayer_frequency',
                'end_date'
            ]);
        });
    }
};