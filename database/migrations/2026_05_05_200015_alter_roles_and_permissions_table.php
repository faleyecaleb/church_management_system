<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'slug')) {
                $table->string('slug')->after('name')->unique()->nullable();
            }
            if (!Schema::hasColumn('roles', 'description')) {
                $table->string('description')->after('slug')->nullable();
            }
            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->after('description')->default(true);
            }
            if (!Schema::hasColumn('roles', 'level')) {
                $table->integer('level')->after('is_active')->default(0);
            }
            if (!Schema::hasColumn('roles', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'slug')) {
                $table->string('slug')->after('name')->unique()->nullable();
            }
            if (!Schema::hasColumn('permissions', 'description')) {
                $table->string('description')->after('slug')->nullable();
            }
            if (!Schema::hasColumn('permissions', 'module')) {
                $table->string('module')->after('description')->nullable();
            }
            if (!Schema::hasColumn('permissions', 'is_active')) {
                $table->boolean('is_active')->after('module')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'is_active', 'level', 'deleted_at']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['slug', 'description', 'module', 'is_active']);
        });
    }
};
