<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:finance.view')->only(['index', 'show', 'report']);
        // $this->middleware('permission:finance.create')->only(['create', 'store']);
        // $this->middleware('permission:finance.update')->only(['edit', 'update', 'approve', 'reject']);
        // $this->middleware('permission:finance.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Expense::with('budget');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('start_date')) {
            $query->where('expense_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('expense_date', '<=', $request->input('end_date'));
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15);
        $budgets = Budget::active()->get();

        return view('expenses.index', compact('expenses', 'budgets'));
    }

    public function create()
    {
        $budgets = Budget::active()->get();
        return view('expenses.create', compact('budgets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string',
            'department' => 'required|string',
            'budget_id' => 'required|exists:budgets,id',
            'payment_method' => 'required|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string'
        ]);

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
            $validated['receipt'] = $path;
        }

        // Check if expense exceeds budget
        $budget = Budget::findOrFail($validated['budget_id']);
        if ($budget->remaining_amount < $validated['amount']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Expense amount exceeds remaining budget.');
        }

        $expense = Expense::create($validated);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense created successfully.');
    }

    public function show(Expense $expense)
    {
        $expense->load('budget');
        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $budgets = Budget::active()->get();
        return view('expenses.edit', compact('expense', 'budgets'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'required|string',
            'department' => 'required|string',
            'budget_id' => 'required|exists:budgets,id',
            'payment_method' => 'required|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'vendor' => 'nullable|string',
            'invoice_number' => 'nullable|string'
        ]);

        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt) {
                Storage::disk('public')->delete($expense->receipt);
            }
            $path = $request->file('receipt')->store('receipts', 'public');
            $validated['receipt'] = $path;
        }

        // Check if expense exceeds budget
        $budget = Budget::findOrFail($validated['budget_id']);
        $budgetDifference = $validated['amount'] - $expense->amount;
        if ($budget->remaining_amount < $budgetDifference) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Updated expense amount exceeds remaining budget.');
        }

        $expense->update($validated);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->receipt) {
            Storage::disk('public')->delete($expense->receipt);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function approve(Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending expenses can be approved.');
        }

        $expense->approve();

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense approved successfully.');
    }

    public function reject(Request $request, Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'Only pending expenses can be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $expense->reject($validated['rejection_reason']);

        return redirect()->route('expenses.show', $expense)
            ->with('success', 'Expense rejected successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->startOfYear();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : now();

        // Expenses by category
        $byCategory = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('category', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->get();

        // Expenses by department
        $byDepartment = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select('department', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('department')
            ->get();

        // Monthly expenses
        $monthlyExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Budget utilization
        $budgetUtilization = Budget::with(['expenses' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }])
            ->get()
            ->map(function ($budget) {
                return [
                    'name' => $budget->name,
                    'allocated' => $budget->amount,
                    'used' => $budget->expenses->sum('amount'),
                    'remaining' => $budget->remaining_amount,
                    'utilization_rate' => $budget->utilization_percentage
                ];
            });

        return view('expenses.report', compact(
            'byCategory',
            'byDepartment',
            'monthlyExpenses',
            'budgetUtilization',
            'startDate',
            'endDate'
        ));
    }
}