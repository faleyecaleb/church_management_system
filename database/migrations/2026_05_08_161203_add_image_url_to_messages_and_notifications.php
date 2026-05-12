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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('content')->comment('Used for visual sermon banners');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('message')->comment('Used for visual push notifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
