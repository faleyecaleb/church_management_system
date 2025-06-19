<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:audit.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:audit.delete')->only(['destroy', 'clear']);
    }

    public function index(Request $request)
    {
        $query = AuditLog::with(['user', 'subject']);

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->input('tag'));
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereJsonContains('changes', $search);
            });
        }

        $logs = $query->orderByDesc('created_at')->paginate(20);
        $users = Member::whereIn('id', AuditLog::distinct('user_id')->pluck('user_id'))
            ->get(['id', 'first_name', 'last_name']);
        $eventTypes = AuditLog::getEventTypes();

        return view('audit-logs.index', compact('logs', 'users', 'eventTypes'));
    }

    public function show(AuditLog $log)
    {
        $log->load(['user', 'subject']);
        return view('audit-logs.show', compact('log'));
    }

    public function destroy(AuditLog $log)
    {
        $log->delete();

        return redirect()->route('audit-logs.index')
            ->with('success', 'Audit log entry deleted successfully.');
    }

    public function clear(Request $request)
    {
        $request->validate([
            'before_date' => 'required|date|before:today'
        ]);

        $count = AuditLog::where('created_at', '<', $request->input('before_date'))->delete();

        return redirect()->route('audit-logs.index')
            ->with('success', "{$count} audit log entries cleared successfully.");
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        // Activity by event type
        $byEventType = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select('event', DB::raw('COUNT(*) as count'))
            ->groupBy('event')
            ->get();

        // Activity by user
        $byUser = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->with('user')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Activity by subject type
        $bySubjectType = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select('subject_type', DB::raw('COUNT(*) as count'))
            ->groupBy('subject_type')
            ->get();

        // Daily activity counts
        $dailyActivity = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most common tags
        $commonTags = AuditLog::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('tags')
            ->select('tags')
            ->get()
            ->flatMap(function ($log) {
                return $log->tags;
            })
            ->countBy()
            ->sortDesc()
            ->take(10);

        return view('audit-logs.report', compact(
            'byEventType',
            'byUser',
            'bySubjectType',
            'dailyActivity',
            'commonTags',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);

        $logs = AuditLog::with(['user:id,first_name,last_name', 'subject'])
            ->whereBetween('created_at', [
                $request->input('start_date'),
                $request->input('end_date')
            ])
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'event' => $log->event,
                    'description' => $log->description,
                    'user' => $log->user ? $log->user->full_name : 'System',
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'changes' => json_encode($log->changes),
                    'tags' => implode(', ', $log->tags ?? []),
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s')
                ];
            });

        $filename = 'audit_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}"
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_keys($logs->first()));

            foreach ($logs as $log) {
                fputcsv($file, $log);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}