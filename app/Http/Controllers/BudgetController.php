<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:finance.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = Budget::withCount('expenses');

        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('fiscal_year')) {
            $query->where('fiscal_year', $request->input('fiscal_year'));
        }

        $budgets = $query->orderBy('fiscal_year', 'desc')
            ->orderBy('category')
            ->paginate(15);

        return view('budgets.index', compact('budgets'));
    }

    public function create()
    {
        return view('budgets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'department' => 'required|string',
            'fiscal_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $budget = Budget::create($validated);

        return redirect()->route('budgets.show', $budget)
            ->with('success', 'Budget created successfully.');
    }

    public function show(Budget $budget)
    {
        $budget->load(['expenses' => function ($query) {
            $query->orderByDesc('expense_date');
        }]);

        $monthlyExpenses = $budget->expenses()
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('budgets.show', compact('budget', 'monthlyExpenses'));
    }

    public function edit(Budget $budget)
    {
        return view('budgets.edit', compact('budget'));
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'department' => 'required|string',
            'fiscal_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Check if reducing budget would cause overspending
        if ($validated['amount'] < $budget->amount && $validated['amount'] < $budget->used_amount) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot reduce budget below current expenses.');
        }

        $budget->update($validated);

        return redirect()->route('budgets.show', $budget)
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Budget $budget)
    {
        // Check if budget has any expenses
        if ($budget->expenses()->exists()) {
            return redirect()->route('budgets.index')
                ->with('error', 'Cannot delete budget with existing expenses.');
        }

        $budget->delete();

        return redirect()->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    public function report(Request $request)
    {
        $fiscalYear = $request->input('fiscal_year', now()->year);

        // Overall budget utilization
        $overallUtilization = Budget::where('fiscal_year', $fiscalYear)
            ->select(
                DB::raw('SUM(amount) as total_budget'),
                DB::raw('SUM(used_amount) as total_used')
            )
            ->first();

        // Budget utilization by category
        $byCategory = Budget::where('fiscal_year', $fiscalYear)
            ->select(
                'category',
                DB::raw('SUM(amount) as total_budget'),
                DB::raw('SUM(used_amount) as total_used')
            )
            ->groupBy('category')
            ->get();

        // Budget utilization by department
        $byDepartment = Budget::where('fiscal_year', $fiscalYear)
            ->select(
                'department',
                DB::raw('SUM(amount) as total_budget'),
                DB::raw('SUM(used_amount) as total_used')
            )
            ->groupBy('department')
            ->get();

        // Monthly budget vs actual
        $monthlyComparison = DB::table('expenses')
            ->join('budgets', 'expenses.budget_id', '=', 'budgets.id')
            ->where('budgets.fiscal_year', $fiscalYear)
            ->select(
                DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month'),
                DB::raw('SUM(expenses.amount) as actual_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Overspent budgets
        $overspentBudgets = Budget::where('fiscal_year', $fiscalYear)
            ->whereRaw('used_amount > amount')
            ->get();

        // Low utilization budgets
        $lowUtilizationBudgets = Budget::where('fiscal_year', $fiscalYear)
            ->whereRaw('(used_amount / amount) < 0.3')
            ->whereDate('end_date', '>', now())
            ->get();

        return view('budgets.report', compact(
            'fiscalYear',
            'overallUtilization',
            'byCategory',
            'byDepartment',
            'monthlyComparison',
            'overspentBudgets',
            'lowUtilizationBudgets'
        ));
    }

    public function createAnnualBudget(Request $request)
    {
        $validated = $request->validate([
            'fiscal_year' => 'required|integer',
            'budgets' => 'required|array',
            'budgets.*.category' => 'required|string',
            'budgets.*.department' => 'required|string',
            'budgets.*.amount' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['budgets'] as $budgetData) {
                Budget::create(array_merge($budgetData, [
                    'fiscal_year' => $validated['fiscal_year'],
                    'start_date' => Carbon::createFromDate($validated['fiscal_year'], 1, 1),
                    'end_date' => Carbon::createFromDate($validated['fiscal_year'], 12, 31),
                    'is_active' => true
                ]));
            }
        });

        return redirect()->route('budgets.index')
            ->with('success', 'Annual budgets created successfully.');
    }
}