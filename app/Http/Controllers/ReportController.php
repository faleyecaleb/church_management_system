<?php

namespace App\Http\Controllers;

use App\Services\ReportingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportingService;

    public function __construct(ReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
        $this->middleware('auth');
    }

    /**
     * Display analytics dashboard
     */
    public function dashboard()
    {
        $stats = $this->reportingService->getDashboardStats();
        return view('reports.dashboard', compact('stats'));
    }

    /**
     * Display membership reports
     */
    public function membership(Request $request)
    {
        $filters = $this->getDateRangeFilters($request);
        $membershipStats = $this->reportingService->getMembershipStats($filters);
        
        return view('reports.membership', [
            'stats' => $membershipStats,
            'filters' => $filters
        ]);
    }

    /**
     * Display attendance reports
     */
    public function attendance(Request $request)
    {
        $filters = $this->getDateRangeFilters($request);
        $attendanceStats = $this->reportingService->getAttendanceStats($filters);

        return view('reports.attendance', [
            'stats' => $attendanceStats,
            'filters' => $filters
        ]);
    }

    /**
     * Display financial reports
     */
    public function financial(Request $request)
    {
        $filters = $this->getDateRangeFilters($request);
        $financialStats = $this->reportingService->getDonationStats($filters);

        return view('reports.financial', [
            'stats' => $financialStats,
            'filters' => $filters
        ]);
    }

    /**
     * Display communication reports
     */
    public function communication(Request $request)
    {
        $filters = $this->getDateRangeFilters($request);
        $messageStats = $this->reportingService->getMessageStats($filters);

        return view('reports.communication', [
            'stats' => $messageStats,
            'filters' => $filters
        ]);
    }

    /**
     * Display growth and engagement reports
     */
    public function growth(Request $request)
    {
        $filters = $this->getDateRangeFilters($request);
        $growthStats = $this->reportingService->getGrowthMetrics($filters);
        $engagementStats = $this->reportingService->getEngagementMetrics($filters);

        return view('reports.growth', [
            'growthStats' => $growthStats,
            'engagementStats' => $engagementStats,
            'filters' => $filters
        ]);
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:membership,attendance,financial,communication,growth',
            'format' => 'required|in:csv,pdf,excel'
        ]);

        $filters = $this->getDateRangeFilters($request);
        $data = [];

        switch ($request->type) {
            case 'membership':
                $data = $this->reportingService->getMembershipStats($filters);
                break;
            case 'attendance':
                $data = $this->reportingService->getAttendanceStats($filters);
                break;
            case 'financial':
                $data = $this->reportingService->getDonationStats($filters);
                break;
            case 'communication':
                $data = $this->reportingService->getMessageStats($filters);
                break;
            case 'growth':
                $data = [
                    'growth' => $this->reportingService->getGrowthMetrics($filters),
                    'engagement' => $this->reportingService->getEngagementMetrics($filters)
                ];
                break;
        }

        return $this->generateReport($data, $request->type, $request->format);
    }

    /**
     * Generate custom report
     */
    public function custom(Request $request)
    {
        $request->validate([
            'metrics' => 'required|array',
            'metrics.*' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'group_by' => 'required|in:day,week,month,year'
        ]);

        $customStats = $this->reportingService->getCustomStats(
            $request->metrics,
            $request->start_date,
            $request->end_date,
            $request->group_by
        );

        return view('reports.custom', [
            'stats' => $customStats,
            'filters' => $request->all()
        ]);
    }

    /**
     * Get date range filters from request
     */
    protected function getDateRangeFilters(Request $request)
    {
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)
            : $endDate->copy()->subMonths(1);

        return [
            'start_date' => $startDate->startOfDay(),
            'end_date' => $endDate->endOfDay(),
            'group_by' => $request->group_by ?? 'day'
        ];
    }

    /**
     * Generate report in specified format
     */
    protected function generateReport($data, $type, $format)
    {
        $filename = sprintf(
            '%s_report_%s.%s',
            $type,
            now()->format('Y-m-d'),
            $format
        );

        switch ($format) {
            case 'csv':
                return response()->streamDownload(function () use ($data) {
                    $this->generateCsvReport($data);
                }, $filename);

            case 'pdf':
                return response()->streamDownload(function () use ($data, $type) {
                    $this->generatePdfReport($data, $type);
                }, $filename);

            case 'excel':
                return response()->streamDownload(function () use ($data, $type) {
                    $this->generateExcelReport($data, $type);
                }, $filename);
        }
    }

    /**
     * Generate CSV report
     */
    protected function generateCsvReport($data)
    {
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array_keys(reset($data)));
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }

    /**
     * Generate PDF report
     */
    protected function generatePdfReport($data, $type)
    {
        // Implement PDF generation logic
        // You might want to use a package like dompdf or mpdf
    }

    /**
     * Generate Excel report
     */
    protected function generateExcelReport($data, $type)
    {
        // Implement Excel generation logic
        // You might want to use Laravel Excel package
    }
}