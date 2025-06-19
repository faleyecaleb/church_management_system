<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pledge extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'campaign_name',
        'total_amount',
        'amount_paid',
        'start_date',
        'end_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDefaulted($query)
    {
        return $query->where('status', 'defaulted');
    }

    public function scopeByCampaign($query, $campaignName)
    {
        return $query->where('campaign_name', $campaignName);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                     ->where('end_date', '<', Carbon::today());
    }

    // Accessors
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    public function getCompletionPercentageAttribute()
    {
        return ($this->amount_paid / $this->total_amount) * 100;
    }

    public function getDaysRemainingAttribute()
    {
        return Carbon::today()->diffInDays($this->end_date, false);
    }

    // Helper methods
    public function recordPayment($amount)
    {
        $newAmountPaid = $this->amount_paid + $amount;
        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $this->determineStatus($newAmountPaid)
        ]);
    }

    protected function determineStatus($newAmountPaid)
    {
        if ($newAmountPaid >= $this->total_amount) {
            return 'completed';
        }

        if (Carbon::today()->isAfter($this->end_date)) {
            return 'defaulted';
        }

        return 'active';
    }

    public static function getCampaignStats($campaignName)
    {
        $pledges = self::byCampaign($campaignName)->get();

        return [
            'total_pledged' => $pledges->sum('total_amount'),
            'total_paid' => $pledges->sum('amount_paid'),
            'total_pledges' => $pledges->count(),
            'completed_pledges' => $pledges->where('status', 'completed')->count(),
            'active_pledges' => $pledges->where('status', 'active')->count(),
            'defaulted_pledges' => $pledges->where('status', 'defaulted')->count(),
            'completion_rate' => $pledges->count() > 0
                ? ($pledges->where('status', 'completed')->count() / $pledges->count()) * 100
                : 0
        ];
    }

    public static function getActiveCampaigns()
    {
        return self::active()
            ->select('campaign_name')
            ->distinct()
            ->get()
            ->pluck('campaign_name');
    }

    public function sendReminderIfNeeded()
    {
        if ($this->status !== 'active') {
            return false;
        }

        $daysToEndDate = $this->days_remaining;
        $completionPercentage = $this->completion_percentage;

        // Send reminder if:
        // 1. Less than 30 days remaining and less than 50% paid
        // 2. Less than 7 days remaining and less than 80% paid
        // 3. Overdue
        if (($daysToEndDate <= 30 && $completionPercentage < 50) ||
            ($daysToEndDate <= 7 && $completionPercentage < 80) ||
            $daysToEndDate < 0) {
            // TODO: Implement notification logic
            return true;
        }

        return false;
    }
}