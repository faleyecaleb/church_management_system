<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:service.view')->only(['index', 'show']);
        $this->middleware('permission:service.create')->only(['create', 'store']);
        $this->middleware('permission:service.update')->only(['edit', 'update']);
        $this->middleware('permission:service.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Service::query();
        dd($request->all());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('day')) {
            $query->where('day_of_week', $request->day);
        }

        if ($request->filled('recurring')) {
            $query->where('is_recurring', $request->boolean('recurring'));
        }

        $services = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(10);

        return view('services.index', compact('services'));
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
            // 'day_of_week'  => 'required|integer|between:0,6',
            'day_of_week' => ['required', Rule::in([
                'sunday','monday','tuesday','wednesday',
                'thursday','friday','saturday'
            ])],
            'start_time'   => 'required|date_format:H:i',
            'end_time'     => 'required|date_format:H:i|after:start_time',
            'location'     => 'required|string|max:255',
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
        $service->load(['attendances' => function ($query) {
            $query->latest('check_in_time')->limit(10);
        }]);

        return view('services.show', compact('service'));
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'is_recurring' => 'required|boolean',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::transaction(function () use ($service, $validated) {
            $service->update($validated);
        });

        return redirect()
            ->route('services.show', $service)
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        // Check if there are any attendances for this service
        if ($service->attendances()->exists()) {
            return back()->with('error', 'Cannot delete service with existing attendance records.');
        }

        DB::transaction(function () use ($service) {
            $service->delete();
        });

        return redirect()
            ->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }
}