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
        Schema::table('order_of_services', function (Blueprint $table) {
            if (!Schema::hasColumn('order_of_services', 'church_id')) {
                $table->foreignId('church_id')->nullable()->after('id')->constrained('churches')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_of_services', function (Blueprint $table) {
            if (Schema::hasColumn('order_of_services', 'church_id')) {
                $table->dropForeign(['church_id']);
                $table->dropColumn('church_id');
            }
        });
    }
};
