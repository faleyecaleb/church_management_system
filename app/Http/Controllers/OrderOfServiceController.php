<?php

namespace App\Http\Controllers;

use App\Models\OrderOfService;
use App\Models\Service;
use Illuminate\Http\Request;

class OrderOfServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:service.view')->only(['index', 'show', 'overview']);
        $this->middleware('permission:service.create')->only(['create', 'store']);
        $this->middleware('permission:service.update')->only(['edit', 'update']);
        $this->middleware('permission:service.delete')->only(['destroy']);
    }

    /**
     * Display an overview of all services and their order of service status.
     */
    public function overview()
    {
        $services = Service::with(['orderOfServices' => function($query) {
            $query->ordered();
        }])
        ->active()
        ->get()
        ->map(function($service) {
            $service->order_count = $service->orderOfServices->count();
            $service->total_duration = $service->orderOfServices->sum('duration');
            return $service;
        });

        return view('order-of-services.overview', compact('services'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Service $service)
    {
        $orderOfServices = $service->orderOfServices()->ordered()->get();
        $totalDuration = $orderOfServices->sum('duration');
        
        return view('order-of-services.index', compact('service', 'orderOfServices', 'totalDuration'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Service $service)
    {
        return view('order-of-services.create', compact('service'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'program' => 'required|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'order' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'leader' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:480', // Max 8 hours
        ]);

        // If no order is provided, auto-assign
        if (!$validated['order']) {
            $maxOrder = $service->orderOfServices()->max('order');
            $validated['order'] = ($maxOrder ?? 0) + 1;
        }

        $service->orderOfServices()->create($validated);

        return redirect()->route('services.order-of-services.index', $service->id)
            ->with('success', 'Order of Service item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderOfService $orderOfService)
    {
        $service = $orderOfService->service;
        return view('order-of-services.show', compact('service', 'orderOfService'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrderOfService $orderOfService)
    {
        $service = $orderOfService->service;
        return view('order-of-services.edit', compact('service', 'orderOfService'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderOfService $orderOfService)
    {
        $validated = $request->validate([
            'program' => 'required|string|max:255',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'order' => 'required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'leader' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'duration_minutes' => 'nullable|integer|min:1|max:480', // Max 8 hours
        ]);

        $orderOfService->update($validated);

        return redirect()->route('services.order-of-services.index', $orderOfService->service_id)
            ->with('success', 'Order of Service item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderOfService $orderOfService)
    {
        $serviceId = $orderOfService->service_id;
        $orderOfService->delete();

        return redirect()->route('services.order-of-services.index', $serviceId)
            ->with('success', 'Order of Service item deleted successfully.');
    }

    /**
     * Reorder the service items.
     */
    public function reorder(Request $request, Service $service)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:order_of_services,id',
            'items.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['items'] as $item) {
            OrderOfService::where('id', $item['id'])
                ->where('service_id', $service->id)
                ->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Duplicate an order of service to another service.
     */
    public function duplicate(Request $request, Service $service)
    {
        $validated = $request->validate([
            'target_service_id' => 'required|exists:services,id',
        ]);

        $targetService = Service::findOrFail($validated['target_service_id']);
        $orderOfServices = $service->orderOfServices()->ordered()->get();

        foreach ($orderOfServices as $item) {
            $targetService->orderOfServices()->create([
                'program' => $item->program,
                'start_time' => $item->start_time,
                'end_time' => $item->end_time,
                'order' => $item->order,
                'description' => $item->description,
                'leader' => $item->leader,
                'notes' => $item->notes,
                'duration_minutes' => $item->duration_minutes,
            ]);
        }

        return redirect()->route('services.order-of-services.index', $targetService->id)
            ->with('success', 'Order of Service duplicated successfully.');
    }

    /**
     * Generate a printable version of the order of service.
     */
    public function print(Service $service)
    {
        $orderOfServices = $service->orderOfServices()->ordered()->get();
        $totalDuration = $orderOfServices->sum('duration');
        
        return view('order-of-services.print', compact('service', 'orderOfServices', 'totalDuration'));
    }
}