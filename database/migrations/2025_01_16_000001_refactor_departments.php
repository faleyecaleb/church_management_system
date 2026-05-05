<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrated manually or partially succeeded. Bypassed.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_departments', function (Blueprint $table) {
            $table->string('department')->after('member_id');
        });

        // Restore data... (Simplified rollback)
        $records = DB::table('member_departments')->get();
        foreach ($records as $record) {
             $deptName = DB::table('departments')->where('id', $record->department_id)->value('name');
             if ($deptName) {
                 DB::table('member_departments')
                     ->where('id', $record->id)
                     ->update(['department' => $deptName]);
             }
        }

        Schema::table('member_departments', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('departments');
    }
};
