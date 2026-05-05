<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->default('web')->change();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (Schema::hasColumn('permissions', 'guard_name')) {
                $table->string('guard_name')->default('web')->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'guard_name')) {
                $table->string('guard_name')->change();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
             if (Schema::hasColumn('permissions', 'guard_name')) {
                 $table->string('guard_name')->change();
             }
        });
    }
};
