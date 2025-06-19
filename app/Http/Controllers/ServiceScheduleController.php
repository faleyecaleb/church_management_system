<?php

namespace App\Http\Controllers;

use App\Models\ServiceSchedule;
use Illuminate\Http\Request;

class ServiceScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:attendance.view')->only(['index', 'show']);
        $this->middleware('permission:attendance.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'toggle']);
    }

    public function index()
    {
        $schedules = ServiceSchedule::orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('services.index', compact('schedules'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        ServiceSchedule::create($validated);

        return redirect()->route('services.index')
            ->with('success', 'Service schedule created successfully.');
    }

    public function show(ServiceSchedule $service)
    {
        $recentAttendance = $service->attendances()
            ->with('member')
            ->orderByDesc('check_in_time')
            ->limit(10)
            ->get();

        $attendanceStats = $service->getAttendanceStats();

        return view('services.show', compact('service', 'recentAttendance', 'attendanceStats'));
    }

    public function edit(ServiceSchedule $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, ServiceSchedule $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $service->update($validated);

        return redirect()->route('services.show', $service)
            ->with('success', 'Service schedule updated successfully.');
    }

    public function destroy(ServiceSchedule $service)
    {
        // Check if there are any attendances linked to this service
        if ($service->attendances()->exists()) {
            return redirect()->route('services.index')
                ->with('error', 'Cannot delete service schedule with existing attendance records.');
        }

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service schedule deleted successfully.');
    }

    public function toggle(ServiceSchedule $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully.',
            'is_active' => $service->is_active
        ]);
    }

    public function calendar(Request $request)
    {
        $start = $request->input('start');
        $end = $request->input('end');

        $services = ServiceSchedule::active()
            ->get()
            ->map(function ($service) use ($start, $end) {
                return $service->getOccurrencesBetween($start, $end);
            })
            ->flatten(1)
            ->map(function ($occurrence) {
                return [
                    'id' => $occurrence['service']->id,
                    'title' => $occurrence['service']->name,
                    'start' => $occurrence['start']->format('Y-m-d\TH:i:s'),
                    'end' => $occurrence['end']->format('Y-m-d\TH:i:s'),
                    'description' => $occurrence['service']->description,
                    'backgroundColor' => $occurrence['service']->is_active ? '#4CAF50' : '#9E9E9E'
                ];
            });

        return response()->json($services);
    }

    public function upcoming()
    {
        $upcomingServices = ServiceSchedule::active()
            ->get()
            ->map(function ($service) {
                return $service->getNextOccurrence();
            })
            ->filter()
            ->sortBy(function ($occurrence) {
                return $occurrence['start'];
            })
            ->take(5);

        return response()->json($upcomingServices);
    }
}