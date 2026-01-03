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
        // Disable foreign key checks to handle existing constraints during re-run
        Schema::disableForeignKeyConstraints();

        // Drop existing departments table if it exists
        Schema::dropIfExists('departments');
        
        // Clean up member_departments if it was partially migrated
        if (Schema::hasColumn('member_departments', 'department_id')) {
             Schema::table('member_departments', function (Blueprint $table) {
                // Drop FK if exists
                try {
                    $table->dropForeign(['department_id']);
                } catch (\Exception $e) {}
                $table->dropColumn('department_id');
             });
        }

        Schema::enableForeignKeyConstraints();

        // 1. Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Seed default departments
        $departments = [
            'Media',
            'Choir',
            'Ushers',
            'Dance',
            'Prayer',
            'Lost but Found',
            'Drama',
            'Sanctuary'
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insert([
                'name' => $dept,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Modify member_departments table
        Schema::table('member_departments', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('member_id')->constrained('departments')->onDelete('cascade');
        });

        // 4. Migrate existing data (if any)
        // Since we had 'department' string column, we try to match it.
        // Assuming member_departments has 'department' column from previous migration.
        if (Schema::hasColumn('member_departments', 'department')) {
             $records = DB::table('member_departments')->get();
             foreach ($records as $record) {
                 $deptId = DB::table('departments')->where('name', $record->department)->value('id');
                 if ($deptId) {
                     DB::table('member_departments')
                         ->where('id', $record->id)
                         ->update(['department_id' => $deptId]);
                 }
             }
             
             
             // 5. Drop the old string column
             Schema::table('member_departments', function (Blueprint $table) {
                $table->dropUnique(['member_id', 'department']); // Drop the unique index first to avoid issues
                $table->dropColumn('department');
             });
        }
        
        // Make department_id not null now and add unique constraint
        Schema::table('member_departments', function (Blueprint $table) {
             $table->foreignId('department_id')->nullable(false)->change();
             $table->unique(['member_id', 'department_id']);
        });
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
