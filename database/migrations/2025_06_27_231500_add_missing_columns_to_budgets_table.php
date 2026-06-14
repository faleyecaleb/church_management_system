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
        if (Schema::hasTable('budgets')) {
            $columns = Schema::getColumnListing('budgets');

            Schema::table('budgets', function (Blueprint $table) use ($columns) {
                // Add missing columns
                if (!in_array('name', $columns)) {
                    $table->string('name')->after('id')->default('Budget Item');
                }
                if (!in_array('fiscal_year', $columns)) {
                    $table->integer('fiscal_year')->after('department')->default(date('Y'));
                }
                if (!in_array('is_active', $columns)) {
                    $table->boolean('is_active')->default(true)->after('notes');
                }
                
                // Rename allocated_amount to amount for consistency with controller
                if (in_array('allocated_amount', $columns) && !in_array('amount', $columns)) {
                    $table->renameColumn('allocated_amount', 'amount');
                }
            });
        }

        // Add budget_id to expenses table for proper relationship if it doesn't exist
        if (Schema::hasTable('expenses')) {
            $expenseColumns = Schema::getColumnListing('expenses');
            if (!in_array('budget_id', $expenseColumns)) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->foreignId('budget_id')->nullable()->after('id')->constrained('budgets')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove budget_id from expenses table
        if (Schema::hasTable('expenses')) {
            $expenseColumns = Schema::getColumnListing('expenses');
            if (in_array('budget_id', $expenseColumns)) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->dropForeign(['budget_id']);
                    $table->dropColumn('budget_id');
                });
            }
        }

        if (Schema::hasTable('budgets')) {
            $columns = Schema::getColumnListing('budgets');
            Schema::table('budgets', function (Blueprint $table) use ($columns) {
                // Remove added columns
                $columnsToDrop = [];
                if (in_array('name', $columns)) $columnsToDrop[] = 'name';
                if (in_array('fiscal_year', $columns)) $columnsToDrop[] = 'fiscal_year';
                if (in_array('is_active', $columns)) $columnsToDrop[] = 'is_active';

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
                
                // Rename back to allocated_amount
                if (in_array('amount', $columns) && !in_array('allocated_amount', $columns)) {
                    $table->renameColumn('amount', 'allocated_amount');
                }
            });
        }
    }
};