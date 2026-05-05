<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'users',
        'members',
        'services',
        'attendances',
        'pledges',
        'expenses',
        'budgets',
        'donations',
        'messages',
        'prayer_requests',
        'complaints',
        'equipment',
        'order_of_services',
        'internal_messages',
        'message_groups',
        'notifications',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'church_id')) {
                        $table->foreignId('church_id')->nullable()->constrained('churches')->onDelete('cascade');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'church_id')) {
                        $table->dropForeign(['church_id']);
                        $table->dropColumn('church_id');
                    }
                });
            }
        }
    }
};
