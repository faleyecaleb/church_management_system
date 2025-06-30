@extends('layouts.admin')

@section('title', 'Order of Service')
@section('header', 'Order of Service')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order of Service</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $service->name }} - {{ $service->day_of_week_name }}s at {{ $service->start_time->format('h:i A') }}</p>
                @if($totalDuration > 0)
                    <p class="mt-1 text-sm text-blue-600">Total Duration: {{ $totalDuration }} minutes</p>
                @endif
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('services.order-of-services.print', $service->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print
                </a>
                <a href="{{ route('services.order-of-services.create', $service->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Item
                </a>
            </div>
        </div>
    </div>

    <!-- Order of Service Items -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg overflow-hidden">
        @if($orderOfServices->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leader</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/30 divide-y divide-gray-200" id="sortable-items">
                        @foreach($orderOfServices as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors" data-id="{{ $item->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-gray-400 mr-2 cursor-move" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ $item->order }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $item->program }}</div>
                                        @if($item->description)
                                            <div class="text-sm text-gray-500 mt-1">{{ Str::limit($item->description, 60) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ $item->leader ?? 'Not assigned' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->start_time && $item->end_time)
                                        <span class="text-sm text-gray-900">{{ $item->time_range }}</span>
                                    @elseif($item->start_time)
                                        <span class="text-sm text-gray-900">{{ $item->start_time->format('h:i A') }}</span>
                                    @else
                                        <span class="text-sm text-gray-500">Time not set</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($item->duration > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $item->duration }} min
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a href="{{ route('order-of-services.edit', $item->id) }}" 
                                       class="text-blue-600 hover:text-blue-900 transition-colors">Edit</a>
                                    <form action="{{ route('order-of-services.destroy', $item->id) }}" 
                                          method="POST" 
                                          class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this item?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No order of service items</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first program item.</p>
                <div class="mt-6">
                    <a href="{{ route('services.order-of-services.create', $service->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add First Item
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Actions -->
    @if($orderOfServices->count() > 0)
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('services.order-of-services.duplicate', $service->id) }}" method="POST" class="inline">
                    @csrf
                    <select name="target_service_id" class="mr-2 px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                        <option value="">Select service to copy to...</option>
                        @foreach(\App\Models\Service::where('id', '!=', $service->id)->active()->get() as $targetService)
                            <option value="{{ $targetService->id }}">{{ $targetService->name }} ({{ $targetService->day_of_week_name }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        Duplicate to Service
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make the table rows sortable
    const sortableList = document.getElementById('sortable-items');
    if (sortableList) {
        Sortable.create(sortableList, {
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function(evt) {
                // Update the order of items
                const items = [];
                const rows = sortableList.querySelectorAll('tr[data-id]');
                
                rows.forEach((row, index) => {
                    items.push({
                        id: row.getAttribute('data-id'),
                        order: index + 1
                    });
                });

                // Send AJAX request to update order
                fetch(`{{ route('services.order-of-services.reorder', $service->id) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ items: items })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the order numbers in the UI
                        rows.forEach((row, index) => {
                            const orderCell = row.querySelector('td:first-child span');
                            orderCell.textContent = index + 1;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error updating order:', error);
                    // Reload page on error
                    location.reload();
                });
            }
        });
    }
});
</script>
@endpush
@endsection
