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

        $services = $query->orderBy('day_of_week')
                         ->orderBy('start_time')
                         ->paginate(12);

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
}
