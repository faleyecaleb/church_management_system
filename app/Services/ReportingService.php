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
    protected function getMembershipStats()
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
    protected function getAttendanceStats()
    {
        $lastSixMonths = now()->subMonths(6);
        $attendances = Attendance::where('date', '>=', $lastSixMonths)->get();

        return [
            'total_services' => $attendances->unique('service_id')->count(),
            'average_attendance' => round($attendances->avg('count')),
            'highest_attendance' => $attendances->max('count'),
            'lowest_attendance' => $attendances->min('count'),
            'attendance_trend' => $this->calculateAttendanceTrend($attendances),
            'service_comparison' => $this->calculateServiceComparison($attendances)
        ];
    }

    /**
     * Get message and communication statistics
     */
    protected function getMessageStats()
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
    protected function getDonationStats()
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
    protected function getGrowthMetrics()
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
    protected function getEngagementMetrics()
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
}