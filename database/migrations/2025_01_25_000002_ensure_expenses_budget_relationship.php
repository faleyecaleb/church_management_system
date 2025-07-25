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
        // Ensure expenses table has budget_id column if it doesn't exist
        if (Schema::hasTable('expenses')) {
            $columns = Schema::getColumnListing('expenses');
            
            if (!in_array('budget_id', $columns)) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->foreignId('budget_id')->nullable()->after('id')->constrained('budgets')->nullOnDelete();
                });
                
                echo "Added budget_id column to expenses table.\n";
            } else {
                echo "budget_id column already exists in expenses table.\n";
            }
            
            // Try to link existing expenses to budgets based on category and department
            if (Schema::hasTable('budgets')) {
                $this->linkExistingExpensesToBudgets();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses')) {
            $columns = Schema::getColumnListing('expenses');
            
            if (in_array('budget_id', $columns)) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->dropForeign(['budget_id']);
                    $table->dropColumn('budget_id');
                });
            }
        }
    }
    
    /**
     * Link existing expenses to budgets based on category and department
     */
    private function linkExistingExpensesToBudgets(): void
    {
        // Get expenses that don't have a budget_id
        $expensesWithoutBudget = DB::table('expenses')
            ->whereNull('budget_id')
            ->get();
            
        foreach ($expensesWithoutBudget as $expense) {
            // Find a matching budget based on category and department
            $budget = DB::table('budgets')
                ->where('category', $expense->category)
                ->where('department', $expense->department)
                ->whereDate('start_date', '<=', $expense->expense_date)
                ->whereDate('end_date', '>=', $expense->expense_date)
                ->first();
                
            if ($budget) {
                // Link the expense to the budget
                DB::table('expenses')
                    ->where('id', $expense->id)
                    ->update(['budget_id' => $budget->id]);
                    
                // Update the budget's used_amount
                DB::table('budgets')
                    ->where('id', $budget->id)
                    ->increment('used_amount', $expense->amount);
            }
        }
    }
};