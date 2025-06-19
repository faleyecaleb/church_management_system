<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'department',
        'allocated_amount',
        'used_amount',
        'start_date',
        'end_date',
        'notes'
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'used_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', Carbon::today())
                     ->where('end_date', '>=', Carbon::today());
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeOverspent($query)
    {
        return $query->whereRaw('used_amount > allocated_amount');
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->allocated_amount - $this->used_amount;
    }

    public function getUtilizationPercentageAttribute()
    {
        return $this->allocated_amount > 0
            ? ($this->used_amount / $this->allocated_amount) * 100
            : 0;
    }

    public function getIsOverspentAttribute()
    {
        return $this->used_amount > $this->allocated_amount;
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::today()->diffInDays($this->end_date, false);
    }

    // Helper methods
    public function incrementUsedAmount($amount)
    {
        $this->increment('used_amount', $amount);

        if ($this->is_overspent) {
            // TODO: Trigger overspent notification
        }
    }

    public function decrementUsedAmount($amount)
    {
        $this->decrement('used_amount', $amount);
    }

    public static function getBudgetStats($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }

        $budgets = $query->get();

        return [
            'total_allocated' => $budgets->sum('allocated_amount'),
            'total_used' => $budgets->sum('used_amount'),
            'total_remaining' => $budgets->sum('remaining_amount'),
            'average_utilization' => $budgets->avg('utilization_percentage'),
            'overspent_categories' => $budgets->where('is_overspent', true)->count(),
            'by_category' => $budgets->groupBy('category')->map(function ($items) {
                return [
                    'allocated' => $items->sum('allocated_amount'),
                    'used' => $items->sum('used_amount'),
                    'remaining' => $items->sum('remaining_amount'),
                    'utilization' => $items->avg('utilization_percentage')
                ];
            }),
            'by_department' => $budgets->groupBy('department')->map(function ($items) {
                return [
                    'allocated' => $items->sum('allocated_amount'),
                    'used' => $items->sum('used_amount'),
                    'remaining' => $items->sum('remaining_amount'),
                    'utilization' => $items->avg('utilization_percentage')
                ];
            })
        ];
    }

    public function checkAndNotifyLowBudget($threshold = 80)
    {
        if ($this->utilization_percentage >= $threshold) {
            // TODO: Implement notification logic for low budget
            return true;
        }

        return false;
    }

    public static function createAnnualBudget($category, $department, $amount, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        
        return self::create([
            'category' => $category,
            'department' => $department,
            'allocated_amount' => $amount,
            'used_amount' => 0,
            'start_date' => Carbon::create($year, 1, 1)->startOfYear(),
            'end_date' => Carbon::create($year, 12, 31)->endOfYear(),
            'notes' => "Annual budget for {$year}"
        ]);
    }

    public function generateMonthlyReport($month = null, $year = null)
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $expenses = Expense::approved()
            ->where('category', $this->category)
            ->where('department', $this->department)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        return [
            'month' => $startDate->format('F Y'),
            'allocated' => $this->allocated_amount,
            'used' => $expenses->sum('amount'),
            'remaining' => $this->remaining_amount,
            'utilization' => $this->utilization_percentage,
            'is_overspent' => $this->is_overspent,
            'expense_breakdown' => $expenses->groupBy('payment_method')
                ->map(fn ($items) => $items->sum('amount'))
        ];
    }
}