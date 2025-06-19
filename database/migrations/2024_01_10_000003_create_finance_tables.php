<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Donations table
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->string('campaign')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('frequency')->nullable(); // weekly, monthly, quarterly, yearly
            $table->date('next_payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pledges table
        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->string('campaign_name');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // active, completed, defaulted
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Expenses table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('department')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('payment_method');
            $table->string('receipt_number')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('description')->nullable();
            $table->string('receipt_file')->nullable(); // path to uploaded receipt
            $table->timestamps();
        });

        // Budget table
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('department')->nullable();
            $table->decimal('allocated_amount', 10, 2);
            $table->decimal('used_amount', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
        Schema::dropIfExists('pledges');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('budgets');
    }
};