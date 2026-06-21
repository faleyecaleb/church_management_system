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
        // Ensure the departments table exists
        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // Ensure the member_departments table has the department_id foreign key column
        if (Schema::hasTable('member_departments')) {
            Schema::table('member_departments', function (Blueprint $table) {
                if (!Schema::hasColumn('member_departments', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('member_id');
                    $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since this is an ensuring/bypassed migration to fix missing local/test schemas,
        // rolling back is not strictly necessary or should be safe.
        if (Schema::hasTable('member_departments')) {
            Schema::table('member_departments', function (Blueprint $table) {
                if (Schema::hasColumn('member_departments', 'department_id')) {
                    $table->dropForeign(['department_id']);
                    $table->dropColumn('department_id');
                }
            });
        }

        Schema::dropIfExists('departments');
    }
};
