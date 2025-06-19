<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'amount',
        'payment_method',
        'transaction_id',
        'campaign',
        'is_recurring',
        'frequency',
        'next_payment_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'next_payment_date' => 'date'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scopes
    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('is_recurring', false);
    }

    public function scopeByCampaign($query, $campaign)
    {
        return $query->where('campaign', $campaign);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeDueForRecurring($query)
    {
        return $query->where('is_recurring', true)
                     ->whereDate('next_payment_date', '<=', Carbon::today());
    }

    // Helper methods
    public static function getTotalDonations($startDate = null, $endDate = null, $campaign = null)
    {
        $query = self::query();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        if ($campaign) {
            $query->where('campaign', $campaign);
        }

        return $query->sum('amount');
    }

    public static function getDonationStats($period = 'month')
    {
        $query = self::query();

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

        $query->where('created_at', '>=', $startDate);

        if ($groupBy === 'date') {
            return $query->selectRaw('DATE(created_at) as date, SUM(amount) as total')
                         ->groupBy('date')
                         ->orderBy('date')
                         ->get();
        }

        return $query->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                     ->groupBy('month')
                     ->orderBy('month')
                     ->get();
    }

    public function updateNextPaymentDate()
    {
        if (!$this->is_recurring) {
            return;
        }

        $nextDate = Carbon::parse($this->next_payment_date);

        switch ($this->frequency) {
            case 'weekly':
                $nextDate->addWeek();
                break;
            case 'monthly':
                $nextDate->addMonth();
                break;
            case 'quarterly':
                $nextDate->addMonths(3);
                break;
            case 'yearly':
                $nextDate->addYear();
                break;
        }

        $this->update(['next_payment_date' => $nextDate]);
    }

    public static function getTopDonors($limit = 10, $period = null)
    {
        $query = self::query()
            ->selectRaw('member_id, SUM(amount) as total_amount')
            ->whereNotNull('member_id')
            ->groupBy('member_id');

        if ($period) {
            $query->where('created_at', '>=', Carbon::now()->sub($period));
        }

        return $query->orderByDesc('total_amount')
                     ->limit($limit)
                     ->with('member')
                     ->get();
    }
}