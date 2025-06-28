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
        Schema::table('budgets', function (Blueprint $table) {
            // Add missing columns
            $table->string('name')->after('id');
            $table->integer('fiscal_year')->after('department');
            $table->boolean('is_active')->default(true)->after('notes');
            
            // Rename allocated_amount to amount for consistency with controller
            $table->renameColumn('allocated_amount', 'amount');
        });

        // Add budget_id to expenses table for proper relationship
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove budget_id from expenses table
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropColumn('budget_id');
        });

        Schema::table('budgets', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn(['name', 'fiscal_year', 'is_active']);
            
            // Rename back to allocated_amount
            $table->renameColumn('amount', 'allocated_amount');
        });
    }
};