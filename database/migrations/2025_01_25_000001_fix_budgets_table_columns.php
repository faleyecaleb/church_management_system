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
        // Check if the budgets table exists and what columns it has
        if (Schema::hasTable('budgets')) {
            $columns = Schema::getColumnListing('budgets');
            
            Schema::table('budgets', function (Blueprint $table) use ($columns) {
                // If allocated_amount exists but amount doesn't, rename it
                if (in_array('allocated_amount', $columns) && !in_array('amount', $columns)) {
                    $table->renameColumn('allocated_amount', 'amount');
                }
                
                // Add missing columns if they don't exist
                if (!in_array('name', $columns)) {
                    $table->string('name')->after('id')->default('Budget Item');
                }
                
                if (!in_array('fiscal_year', $columns)) {
                    $table->integer('fiscal_year')->after('department')->default(date('Y'));
                }
                
                if (!in_array('is_active', $columns)) {
                    $table->boolean('is_active')->after('notes')->default(true);
                }
                
                // Ensure used_amount exists
                if (!in_array('used_amount', $columns)) {
                    $table->decimal('used_amount', 10, 2)->after('amount')->default(0);
                }
            });
            
            // Update any records that might have null names
            DB::table('budgets')
                ->whereNull('name')
                ->orWhere('name', '')
                ->update(['name' => DB::raw("CONCAT(category, ' - ', department)")]);
        }
        
        // Also ensure expenses table has budget_id column
        if (Schema::hasTable('expenses')) {
            $expenseColumns = Schema::getColumnListing('expenses');
            
            if (!in_array('budget_id', $expenseColumns)) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->foreignId('budget_id')->nullable()->after('id')->constrained()->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove budget_id from expenses table if we added it
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
            Schema::table('budgets', function (Blueprint $table) {
                // Only drop columns if they exist
                $columns = Schema::getColumnListing('budgets');
                
                if (in_array('name', $columns)) {
                    $table->dropColumn('name');
                }
                
                if (in_array('fiscal_year', $columns)) {
                    $table->dropColumn('fiscal_year');
                }
                
                if (in_array('is_active', $columns)) {
                    $table->dropColumn('is_active');
                }
                
                // Rename amount back to allocated_amount if needed
                if (in_array('amount', $columns) && !in_array('allocated_amount', $columns)) {
                    $table->renameColumn('amount', 'allocated_amount');
                }
            });
        }
    }
};