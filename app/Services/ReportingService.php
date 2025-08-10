<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\Message;
use App\Models\Donation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Get dashboard overview statistics
     */
    public function getDashboardStats()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        return [
            'members' => $this->getMembershipStats(),
            'attendance' => $this->getAttendanceStats(),
            'messages' => $this->getMessageStats(),
            'donations' => $this->getDonationStats(),
            'growth' => $this->getGrowthMetrics(),
            'engagement' => $this->getEngagementMetrics()
        ];
    }

    /**
     * Get membership statistics
     */
    public function getMembershipStats($filters = null)
    {
        $members = Member::all();
        $activeMembers = $members->where('status', 'active');

        return [
            'total_members' => $members->count(),
            'active_members' => $activeMembers->count(),
            'inactive_members' => $members->where('status', 'inactive')->count(),
            'new_members_this_month' => Member::whereMonth('created_at', now()->month)->count(),
            'demographics' => [
                'age_groups' => $this->calculateAgeGroups($activeMembers),
                'gender_distribution' => $this->calculateGenderDistribution($activeMembers)
            ],
            'membership_duration' => $this->calculateMembershipDuration($activeMembers)
        ];
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats($filters = null)
    {
        $start = $filters['start_date'] ?? now()->subMonths(6)->startOfDay();
        $attendanceQuery = Attendance::query();
        // Use attendance_date if available; otherwise fallback to check_in_time
        $dateColumn = 'attendance_date';
        if (!\Schema::hasColumn('attendances', 'attendance_date')) {
            $dateColumn = 'check_in_time';
        }
        $attendanceQuery->where($dateColumn, '>=', $start);

        // Build daily totals
        $daily = $attendanceQuery->clone()
            ->selectRaw(($dateColumn === 'attendance_date' ? "$dateColumn" : "DATE($dateColumn)") . ' as day, COUNT(*) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $totals = $daily->pluck('total');
        $serviceComparison = Attendance::selectRaw('service_id, COUNT(*) as total')
            ->where($dateColumn, '>=', $start)
            ->groupBy('service_id')
            ->pluck('total', 'service_id');

        return [
            'total_records' => Attendance::where($dateColumn, '>=', $start)->count(),
            'total_services' => $daily->count(),
            'average_attendance' => $totals->count() ? round($totals->avg()) : 0,
            'highest_attendance' => $totals->max() ?? 0,
            'lowest_attendance' => $totals->min() ?? 0,
            'attendance_trend' => $daily->map(fn($r) => ['day' => (string)$r->day, 'total' => (int)$r->total])->values(),
            'service_comparison' => $serviceComparison,
        ];
    }

    /**
     * Get message and communication statistics
     */
    public function getMessageStats($filters = null)
    {
        $messages = Message::whereMonth('created_at', now()->month)->get();

        return [
            'total_messages' => $messages->count(),
            'by_type' => [
                'sms' => $messages->where('type', 'sms')->count(),
                'prayer' => $messages->where('type', 'prayer')->count(),
                'internal' => $messages->where('type', 'internal')->count()
            ],
            'delivery_stats' => [
                'delivered' => $messages->where('status', 'delivered')->count(),
                'failed' => $messages->where('status', 'failed')->count(),
                'pending' => $messages->where('status', 'pending')->count()
            ],
            'engagement_rate' => $this->calculateMessageEngagementRate($messages)
        ];
    }

    /**
     * Get donation and financial statistics
     */
    public function getDonationStats($filters = null)
    {
        $donations = Donation::whereMonth('created_at', now()->month)->get();

        return [
            'total_amount' => $donations->sum('amount'),
            'average_donation' => $donations->avg('amount'),
            'donor_count' => $donations->unique('donor_id')->count(),
            'by_category' => $this->calculateDonationsByCategory($donations),
            'trend' => $this->calculateDonationTrend(),
            'campaign_performance' => $this->calculateCampaignPerformance()
        ];
    }

    /**
     * Calculate growth metrics
     */
    public function getGrowthMetrics($filters = null)
    {
        $now = Carbon::now();
        $lastYear = $now->copy()->subYear();

        $currentYearMembers = Member::whereYear('created_at', $now->year)->count();
        $lastYearMembers = Member::whereYear('created_at', $lastYear->year)->count();

        return [
            'year_over_year_growth' => $this->calculateGrowthPercentage($lastYearMembers, $currentYearMembers),
            'monthly_growth_rate' => $this->calculateMonthlyGrowthRate(),
            'retention_rate' => $this->calculateRetentionRate(),
            'conversion_rate' => $this->calculateConversionRate()
        ];
    }

    /**
     * Calculate member engagement metrics
     */
    public function getEngagementMetrics($filters = null)
    {
        return [
            'service_attendance' => $this->calculateAttendanceEngagement(),
            'giving_participation' => $this->calculateGivingParticipation(),
            'ministry_involvement' => $this->calculateMinistryInvolvement(),
            'event_participation' => $this->calculateEventParticipation(),
            'communication_engagement' => $this->calculateCommunicationEngagement()
        ];
    }

    /**
     * Helper method to calculate age group distribution
     */
    protected function calculateAgeGroups($members)
    {
        $ageGroups = [
            '0-18' => 0,
            '19-30' => 0,
            '31-50' => 0,
            '51-70' => 0,
            '70+' => 0
        ];

        foreach ($members as $member) {
            $age = Carbon::parse($member->date_of_birth)->age;
            switch (true) {
                case $age <= 18:
                    $ageGroups['0-18']++;
                    break;
                case $age <= 30:
                    $ageGroups['19-30']++;
                    break;
                case $age <= 50:
                    $ageGroups['31-50']++;
                    break;
                case $age <= 70:
                    $ageGroups['51-70']++;
                    break;
                default:
                    $ageGroups['70+']++;
            }
        }

        return $ageGroups;
    }

    /**
     * Helper method to calculate gender distribution
     */
    protected function calculateGenderDistribution($members)
    {
        return [
            'male' => $members->where('gender', 'male')->count(),
            'female' => $members->where('gender', 'female')->count(),
            'other' => $members->where('gender', 'other')->count()
        ];
    }

    /**
     * Helper method to calculate membership duration distribution
     */
    protected function calculateMembershipDuration($members)
    {
        $durations = [
            '<1 year' => 0,
            '1-5 years' => 0,
            '5-10 years' => 0,
            '>10 years' => 0
        ];

        foreach ($members as $member) {
            $years = Carbon::parse($member->join_date)->diffInYears(now());
            switch (true) {
                case $years < 1:
                    $durations['<1 year']++;
                    break;
                case $years <= 5:
                    $durations['1-5 years']++;
                    break;
                case $years <= 10:
                    $durations['5-10 years']++;
                    break;
                default:
                    $durations['>10 years']++;
            }
        }

        return $durations;
    }

    /**
     * Helper method to calculate growth percentage
     */
    protected function calculateGrowthPercentage($old, $new)
    {
        if ($old == 0) return 100;
        return round((($new - $old) / $old) * 100, 2);
    }

    /**
     * Helper method to calculate monthly growth rate
     */
    protected function calculateMonthlyGrowthRate()
    {
        $months = collect(range(1, 12))->map(function($month) {
            $date = Carbon::create(null, $month, 1);
            return [
                'month' => $date->format('M'),
                'count' => Member::whereMonth('created_at', $month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ];
        });

        return $months->pluck('count', 'month')->toArray();
    }

    /*
     |--------------------------------------------------------------------------
     | Basic helper calculators to prevent missing-method errors
     |--------------------------------------------------------------------------
     */
    protected function calculateMessageEngagementRate($messages)
    {
        $total = $messages->count();
        if ($total === 0) return 0;
        $delivered = $messages->where('status', 'delivered')->count();
        return round(($delivered / max($total, 1)) * 100, 2);
    }

    protected function calculateDonationTrend()
    {
        return Donation::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get();
    }

    protected function calculateDonationsByCategory($donations)
    {
        // Use campaign as a proxy for category if no category column exists
        return $donations
            ->groupBy(function($d){ return $d->campaign ?? 'Uncategorized'; })
            ->map(fn($g) => $g->sum('amount'));
    }

    protected function calculateCampaignPerformance()
    {
        return Donation::selectRaw('COALESCE(campaign, "Uncategorized") as campaign, SUM(amount) as total')
            ->groupBy('campaign')
            ->orderByDesc('total')
            ->get();
    }

    protected function calculateRetentionRate()
    {
        $total = Member::count();
        if ($total === 0) return 0;
        $retained = Member::where('created_at', '<=', now()->subYear())->count();
        return round(($retained / max($total,1)) * 100, 2);
    }

    protected function calculateConversionRate()
    {
        $total = Member::count();
        if ($total === 0) return 0;
        $newThisMonth = Member::whereMonth('created_at', now()->month)->count();
        return round(($newThisMonth / max($total,1)) * 100, 2);
    }

    protected function calculateAttendanceEngagement()
    {
        // Percentage of days with at least one attendance record in the last 30 days
        $daysWithAttendance = Attendance::selectRaw('DATE(COALESCE(attendance_date, check_in_time)) as day')
            ->whereRaw('COALESCE(attendance_date, check_in_time) >= ?', [now()->subDays(30)])
            ->groupBy('day')
            ->get()
            ->count();
        return round(($daysWithAttendance / 30) * 100, 2);
    }

    protected function calculateGivingParticipation()
    {
        $activeMembers = max(Member::count(), 1);
        $donorsThisMonth = Donation::whereMonth('created_at', now()->month)
            ->whereNotNull('member_id')
            ->distinct('member_id')
            ->count('member_id');
        return round(($donorsThisMonth / $activeMembers) * 100, 2);
    }

    protected function calculateMinistryInvolvement()
    {
        // Placeholder: no ministries table here
        return 0;
    }

    protected function calculateEventParticipation()
    {
        // Placeholder: no events tracking table here
        return 0;
    }

    protected function calculateCommunicationEngagement()
    {
        $total = Message::count();
        if ($total === 0) return 0;
        $delivered = Message::where('status', 'delivered')->count();
        return round(($delivered / max($total,1)) * 100, 2);
    }
}