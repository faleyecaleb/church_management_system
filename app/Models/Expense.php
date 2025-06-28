<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'budget_id',
        'category',
        'department',
        'amount',
        'expense_date',
        'payment_method',
        'receipt_number',
        'approved_by',
        'status',
        'description',
        'receipt_file'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date'
    ];

    // Relationships
    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    // Helper methods
    public function approve($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy
        ]);

        // Update related budget
        $budget = Budget::where('category', $this->category)
            ->where('department', $this->department)
            ->where('start_date', '<=', $this->expense_date)
            ->where('end_date', '>=', $this->expense_date)
            ->first();

        if ($budget) {
            $budget->incrementUsedAmount($this->amount);
        }

        // TODO: Trigger notification to expense creator
    }

    public function reject($rejectedBy, $reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy,
            'notes' => $reason
        ]);

        // TODO: Trigger notification to expense creator
    }

    public static function getExpenseStats($period = 'month')
    {
        $query = self::approved();

        switch ($period) {
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $groupBy = 'date';
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $groupBy = 'date';
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $groupBy = 'month';
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $groupBy = 'date';
        }

        $query->where('expense_date', '>=', $startDate);

        return [
            'total_amount' => $query->sum('amount'),
            'by_category' => $query->groupBy('category')
                                  ->selectRaw('category, SUM(amount) as total')
                                  ->pluck('total', 'category'),
            'by_department' => $query->groupBy('department')
                                    ->selectRaw('department, SUM(amount) as total')
                                    ->pluck('total', 'department'),
            'by_payment_method' => $query->groupBy('payment_method')
                                        ->selectRaw('payment_method, SUM(amount) as total')
                                        ->pluck('total', 'payment_method')
        ];
    }

    public static function getTopExpenseCategories($limit = 5, $startDate = null, $endDate = null)
    {
        $query = self::approved()
            ->selectRaw('category, SUM(amount) as total_amount')
            ->groupBy('category');

        if ($startDate && $endDate) {
            $query->whereBetween('expense_date', [$startDate, $endDate]);
        }

        return $query->orderByDesc('total_amount')
                     ->limit($limit)
                     ->get();
    }

    public function isOverBudget()
    {
        $budget = Budget::where('category', $this->category)
            ->where('department', $this->department)
            ->where('start_date', '<=', $this->expense_date)
            ->where('end_date', '>=', $this->expense_date)
            ->first();

        if (!$budget) {
            return false;
        }

        return ($budget->used_amount + $this->amount) > $budget->amount;
    }
}