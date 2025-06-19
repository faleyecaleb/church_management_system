@extends('layouts.admin')

@section('title', 'Services')
@section('header', 'Service Management')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Church Services</h2>
                <p class="text-sm text-gray-500 mt-1">Manage service schedules and details</p>
            </div>
            @can('service.create')
            <a href="{{ route('services.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add New Service
            </a>
            @endcan
        </div>

        <!-- Filter Form -->
        <form x-data="{ showFilters: false }" action="{{ route('services.index') }}" method="GET" class="mb-6 bg-white/50 backdrop-blur-sm rounded-2xl p-4 border border-gray-100/20 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-700">Filter Services</h3>
                <button @click.prevent="showFilters = !showFilters" type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <span x-show="!showFilters">Show Filters</span>
                    <span x-show="showFilters">Hide Filters</span>
                    <svg class="w-5 h-5 inline-block ml-1" :class="{'rotate-180': showFilters}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
            
            <div x-show="showFilters" x-transition class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <div>
                        <label for="day" class="block text-sm font-medium text-gray-700 mb-1">Day of Week</label>
                        <select name="day" id="day" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all duration-300">
                            <option value="">All Days</option>
                            <option value="sunday" {{ request('day') === 'sunday' ? 'selected' : '' }}>Sunday</option>
                            <option value="monday" {{ request('day') === 'monday' ? 'selected' : '' }}>Monday</option>
                            <option value="tuesday" {{ request('day') === 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                            <option value="wednesday" {{ request('day') === 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                            <option value="thursday" {{ request('day') === 'thursday' ? 'selected' : '' }}>Thursday</option>
                            <option value="friday" {{ request('day') === 'friday' ? 'selected' : '' }}>Friday</option>
                            <option value="saturday" {{ request('day') === 'saturday' ? 'selected' : '' }}>Saturday</option>
                        </select>
                    </div>
                    <div>
                        <label for="time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                        <select name="time" id="time" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all duration-300">
                            <option value="">All Times</option>
                            <option value="morning" {{ request('time') === 'morning' ? 'selected' : '' }}>Morning</option>
                            <option value="afternoon" {{ request('time') === 'afternoon' ? 'selected' : '' }}>Afternoon</option>
                            <option value="evening" {{ request('time') === 'evening' ? 'selected' : '' }}>Evening</option>
                        </select>
                    </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" id="location" value="{{ request('location') }}" 
                               class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all duration-300" 
                               placeholder="Search by location">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all duration-300">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label for="recurring" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select name="recurring" id="recurring" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-all duration-300">
                            <option value="">All Types</option>
                            <option value="1" {{ request('recurring') === '1' ? 'selected' : '' }}>Recurring</option>
                            <option value="0" {{ request('recurring') === '0' ? 'selected' : '' }}>One-time</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-300 shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Apply
                        </button>
                        <a href="{{ route('services.index') }}" class="px-4 py-2 text-gray-600 hover:text-gray-800 rounded-xl hover:bg-gray-100 transition-all duration-300">
                            Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <!-- Services List -->
        <div class="overflow-hidden rounded-2xl border border-gray-100/20 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200/50">
                    <thead class="bg-gray-50/50 backdrop-blur-sm">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Capacity</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/50 backdrop-blur-sm divide-y divide-gray-200/50">
                        @forelse($services as $service)
                        <tr class="hover:bg-blue-50/50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $service->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $service->day }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $service->start_time }} - {{ $service->end_time }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $service->location }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->capacity ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $service->capacity ? $service->capacity . ' seats' : 'Unlimited' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('services.show', $service) }}" class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                    @can('edit services')
                                    <a href="{{ route('services.edit', $service) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    @endcan
                                    @can('delete services')
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-900 transition-colors duration-200" 
                                                onclick="return confirm('Are you sure you want to delete this service?')">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 whitespace-nowrap text-sm text-center">
                                <div class="max-w-sm mx-auto text-center">
                                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <p class="mt-4 text-gray-500">No services found.</p>
                                    <a href="{{ route('services.create') }}" class="mt-2 inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                        Create your first service
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection