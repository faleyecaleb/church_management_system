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
        Schema::table('members', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique()->after('id')->comment('For barcode or fingerprint scanner input');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('service_type')->default('regular')->after('name')->comment('regular, sunday_school, special');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
};
