@extends('layouts.admin')

@section('title', 'Services')
@section('header', 'Church Services')

@push('styles')
<style>
    .service-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Church Services</h1>
            <p class="mt-1 text-sm text-gray-500">Manage and view all church services.</p>
        </div>
        <a href="{{ route('services.create') }}" class="mt-4 md:mt-0 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-transform duration-300 hover:scale-105">
            <svg class="-ml-1 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Service
        </a>
    </div>

    <!-- Enhanced Search and Filter -->
    <div class="mb-6 bg-white p-6 rounded-lg shadow-sm">
        <form action="{{ route('services.index') }}" method="GET" id="filter-form">
            <!-- Month/Year Selection -->
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Filter by Period</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Month Selection -->
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                        <select name="month" id="month" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Months</option>
                            <option value="1" {{ request('month', $month) == 1 ? 'selected' : '' }}>January</option>
                            <option value="2" {{ request('month', $month) == 2 ? 'selected' : '' }}>February</option>
                            <option value="3" {{ request('month', $month) == 3 ? 'selected' : '' }}>March</option>
                            <option value="4" {{ request('month', $month) == 4 ? 'selected' : '' }}>April</option>
                            <option value="5" {{ request('month', $month) == 5 ? 'selected' : '' }}>May</option>
                            <option value="6" {{ request('month', $month) == 6 ? 'selected' : '' }}>June</option>
                            <option value="7" {{ request('month', $month) == 7 ? 'selected' : '' }}>July</option>
                            <option value="8" {{ request('month', $month) == 8 ? 'selected' : '' }}>August</option>
                            <option value="9" {{ request('month', $month) == 9 ? 'selected' : '' }}>September</option>
                            <option value="10" {{ request('month', $month) == 10 ? 'selected' : '' }}>October</option>
                            <option value="11" {{ request('month', $month) == 11 ? 'selected' : '' }}>November</option>
                            <option value="12" {{ request('month', $month) == 12 ? 'selected' : '' }}>December</option>
                        </select>
                    </div>
                    
                    <!-- Year Selection -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <select name="year" id="year" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Years</option>
                            @for($yearOption = now()->year - 2; $yearOption <= now()->year + 2; $yearOption++)
                                <option value="{{ $yearOption }}" {{ request('year', $year) == $yearOption ? 'selected' : '' }}>{{ $yearOption }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <!-- Day of Week Filter -->
                    <div>
                        <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">Day of Week</label>
                        <select name="day_of_week" id="day_of_week" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Days</option>
                            <option value="0" {{ request('day_of_week') == '0' ? 'selected' : '' }}>Sunday</option>
                            <option value="1" {{ request('day_of_week') == '1' ? 'selected' : '' }}>Monday</option>
                            <option value="2" {{ request('day_of_week') == '2' ? 'selected' : '' }}>Tuesday</option>
                            <option value="3" {{ request('day_of_week') == '3' ? 'selected' : '' }}>Wednesday</option>
                            <option value="4" {{ request('day_of_week') == '4' ? 'selected' : '' }}>Thursday</option>
                            <option value="5" {{ request('day_of_week') == '5' ? 'selected' : '' }}>Friday</option>
                            <option value="6" {{ request('day_of_week') == '6' ? 'selected' : '' }}>Saturday</option>
                        </select>
                    </div>
                    
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" id="status" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Search and Action Buttons -->
            <div class="border-t pt-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="search" name="search" id="search" value="{{ request('search') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search by service name...">
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('services.index') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 9a9 9 0 0114.13-6.36M20 15a9 9 0 01-14.13 6.36"></path>
                            </svg>
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg border border-blue-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="text-blue-800 font-medium">
                    Showing {{ $services->count() }} of {{ $services->total() }} services
                    @if(request('month') || request('year'))
                        for 
                        @if(request('month'))
                            {{ DateTime::createFromFormat('!m', request('month', $month))->format('F') }}
                        @endif
                        @if(request('year'))
                            {{ request('year', $year) }}
                        @endif
                    @endif
                </span>
            </div>
            @if(request()->hasAny(['search', 'month', 'year', 'day_of_week', 'status']))
                <div class="text-sm text-blue-600">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Filtered Results
                    </span>
                </div>
            @endif
        </div>
    </div>

    <!-- Services Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($services as $service)
        <div class="service-card bg-white rounded-xl shadow-lg overflow-hidden border-l-4 {{ $service->status === 'active' ? 'border-green-500' : 'border-red-500' }}">
            <div class="p-6">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-800 mb-1">{{ $service->name }}</h3>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">
                                {{ ucfirst($service->day_of_week_name) }}s
                            </span>
                            @if($service->is_recurring)
                                <span class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full">
                                    Recurring
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($service->status) }}
                    </span>
                </div>
                
                @if($service->description)
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $service->description }}</p>
                @endif
                
                <div class="space-y-2">
                    <div class="flex items-center text-gray-500">
                        <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}</span>
                    </div>
                    
                    @if($service->location)
                        <div class="flex items-center text-gray-500">
                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm">{{ $service->location }}</span>
                        </div>
                    @endif
                    
                    @if($service->capacity)
                        <div class="flex items-center text-gray-500">
                            <svg class="h-4 w-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span class="text-sm">Capacity: {{ $service->capacity }}</span>
                        </div>
                    @endif
                </div>

                <!-- Show next occurrence if filtering by current month/year -->
                @if(request('month') == now()->month && request('year') == now()->year && $service->is_recurring)
                    @php
                        $nextOccurrence = $service->next_occurrence;
                    @endphp
                    @if($nextOccurrence)
                        <div class="mt-3 p-2 bg-blue-50 rounded-lg">
                            <div class="flex items-center text-blue-700">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs font-medium">Next: {{ $nextOccurrence->format('M j, Y') }}</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            
            <div class="bg-gray-50 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <a href="{{ route('services.order-of-services.index', $service) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Order
                        </a>
                        <a href="{{ route('services.edit', $service) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit
                        </a>
                    </div>
                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors" onclick="return confirm('Are you sure you want to delete this service?')">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No services found</h3>
            <p class="mt-1 text-sm text-gray-500">
                @if(request()->hasAny(['search', 'month', 'year', 'day_of_week', 'status']))
                    Try adjusting your filters or <a href="{{ route('services.index') }}" class="text-indigo-600 hover:text-indigo-500">clear all filters</a>.
                @else
                    Get started by creating a new service.
                @endif
            </p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $services->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change (optional - can be enabled for better UX)
    const autoSubmitFilters = ['month', 'year', 'day_of_week', 'status'];
    
    autoSubmitFilters.forEach(filterId => {
        const element = document.getElementById(filterId);
        if (element) {
            element.addEventListener('change', function() {
                // Optional: Auto-submit on filter change
                // Uncomment the line below if you want automatic filtering
                // document.getElementById('filter-form').submit();
            });
        }
    });

    // Enhanced search with debouncing
    let searchTimeout;
    const searchInput = document.getElementById('search');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Optional: Auto-submit on search (with debouncing)
                // Uncomment the line below if you want automatic search
                // document.getElementById('filter-form').submit();
            }, 500); // Wait 500ms after user stops typing
        });
    }

    // Add loading state to filter button
    const filterForm = document.getElementById('filter-form');
    const filterButton = filterForm.querySelector('button[type="submit"]');
    
    if (filterForm && filterButton) {
        filterForm.addEventListener('submit', function() {
            filterButton.disabled = true;
            filterButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Filtering...
            `;
        });
    }

    // Highlight active filters
    function highlightActiveFilters() {
        const filters = ['month', 'year', 'day_of_week', 'status', 'search'];
        
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element && element.value) {
                element.classList.add('ring-2', 'ring-indigo-500', 'border-indigo-500');
                element.classList.remove('border-gray-300');
            }
        });
    }

    highlightActiveFilters();
});
</script>
@endpush
