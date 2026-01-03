<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Day of week filter
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        // Month and year filtering (for recurring services)
        $month = $request->filled('month') ? $request->month : now()->month;
        $year = $request->filled('year') ? $request->year : now()->year;

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'schedule':
                $query->orderBy('day_of_week')
                      ->orderBy('start_time');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $services = $query->paginate(12);

        return view('services.index', compact('services', 'month', 'year'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'day_of_week' => ['required', Rule::in([
                'sunday','monday','tuesday','wednesday',
                'thursday','friday','saturday'
            ])],
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'location'     => 'nullable|string|max:255',
            'is_recurring' => 'required|boolean',
            'date'         => 'nullable|required_if:is_recurring,0|date|after_or_equal:today',
            'capacity'     => 'nullable|integer|min:1',
            'status'       => 'required|in:active,inactive',
            'notes'        => 'nullable|string|max:1000',
        ]);

        // map name → integer
        $dayMap = [
            'sunday'    => 0,
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
        ];
        $validated['day_of_week'] = $dayMap[$validated['day_of_week']];

        DB::transaction(function () use ($validated) {
            Service::create($validated);
        });

        return redirect()
            ->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string|max:1000',
            'day_of_week' => ['required', Rule::in([
                'sunday','monday','tuesday','wednesday',
                'thursday','friday','saturday'
            ])],
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'location'     => 'nullable|string|max:255',
            'is_recurring' => 'required|boolean',
            'date'         => 'nullable|required_if:is_recurring,0|date|after_or_equal:today',
            'capacity'     => 'nullable|integer|min:1',
            'status'       => 'required|in:active,inactive',
            'notes'        => 'nullable|string|max:1000',
        ]);

        // map name → integer
        $dayMap = [
            'sunday'    => 0,
            'monday'    => 1,
            'tuesday'   => 2,
            'wednesday' => 3,
            'thursday'  => 4,
            'friday'    => 5,
            'saturday'  => 6,
        ];
        $validated['day_of_week'] = $dayMap[$validated['day_of_week']];

        DB::transaction(function () use ($service, $validated) {
            $service->update($validated);
        });

        return redirect()
            ->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        DB::transaction(function () use ($service) {
            $service->delete();
        });

        return redirect()
            ->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }

    public function calendar()
    {
        return view('services.calendar');
    }

    public function events(Request $request)
    {
        $start = \Illuminate\Support\Carbon::parse($request->start);
        $end = \Illuminate\Support\Carbon::parse($request->end);

        $services = Service::where('status', 'active')->get();
        $events = [];

        foreach ($services as $service) {
            if ($service->status !== 'active') continue;

            if (!$service->is_recurring) {
                if ($service->date) {
                    $serviceDate = \Illuminate\Support\Carbon::parse($service->date);
                    if ($serviceDate->between($start, $end)) {
                         $startDateTime = $serviceDate->copy()->setTimeFromTimeString($service->start_time->format('H:i:s'));
                         $endDateTime = $serviceDate->copy()->setTimeFromTimeString($service->end_time->format('H:i:s'));
                         
                         $events[] = [
                            'id' => $service->id . '_single',
                            'title' => $service->name . ' (One-time)',
                            'start' => $startDateTime->toIso8601String(),
                            'end' => $endDateTime->toIso8601String(),
                            'url' => route('services.show', $service),
                            'backgroundColor' => '#10b981', // emerald-500
                            'borderColor' => '#059669', // emerald-600
                            'extendedProps' => [
                                'location' => $service->location,
                                'description' => $service->description,
                            ]
                        ];
                    }
                }
                continue;
            }

            $currentDate = $start->copy();
            
            while ($currentDate->lte($end)) {
                if ($currentDate->dayOfWeek === $service->day_of_week) {
                    $startDateTime = $currentDate->copy()->setTimeFromTimeString($service->start_time->format('H:i:s'));
                    $endDateTime = $currentDate->copy()->setTimeFromTimeString($service->end_time->format('H:i:s'));

                    $events[] = [
                        'id' => $service->id . '_' . $currentDate->format('Y-m-d'),
                        'title' => $service->name,
                        'start' => $startDateTime->toIso8601String(),
                        'end' => $endDateTime->toIso8601String(),
                        'url' => route('services.show', $service),
                        'backgroundColor' => '#4f46e5',
                        'borderColor' => '#4338ca',
                        'extendedProps' => [
                            'location' => $service->location,
                            'description' => $service->description,
                        ]
                    ];
                }
                $currentDate->addDay();
            }
        }

        return response()->json($events);
    }

    public function ajaxFilter(Request $request) 
    {
        try {
            $year = $request->input('year');
            $month = $request->input('month');
            $excludeId = $request->input('exclude_id');

            $query = Service::query();

            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }

            $query->where(function($q) use ($year, $month) {
                // Recurring services are always available
                $q->where('is_recurring', 1)
                  ->where('status', 'active');
                
                // One-time services match the date
                if ($year && $month) {
                    $q->orWhere(function($subQ) use ($year, $month) {
                        $subQ->where('is_recurring', 0)
                             ->whereYear('date', $year)
                             ->whereMonth('date', $month);
                    });
                }
            });

            $services = $query->orderBy('is_recurring', 'desc') // Recurring first
                              ->orderBy('date', 'asc')
                              ->get()
                              ->map(function($service) {
                                  return [
                                      'id' => $service->id,
                                      'name' => $service->name,
                                      'info' => $service->is_recurring 
                                          ? "{$service->day_of_week_name}s (Recurring)" 
                                          : (optional($service->date)->format('M j, Y') ?? 'Date Not Set') . " (One-time)"
                                  ];
                              });

            return response()->json($services);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Service Filter Error: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage() // Return error for debugging
            ], 500); 
        }
    }
}
