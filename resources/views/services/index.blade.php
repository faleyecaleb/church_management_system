@extends('layouts.admin')

@section('title', 'Services')

@push('styles')
<style>
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb {
        background: #c7c7c7; 
        border-radius: 4px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }

    /* Glassmorphism & Gradients */
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    .service-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.5s ease-out forwards;
    }
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-300 { animation-delay: 300ms; }
</style>
@endpush

@section('content')
<div class="min-h-screen pb-12">
    <!-- Top Hero Section -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-800 pb-32 pt-12 px-4 sm:px-6 lg:px-8 rounded-b-3xl shadow-xl">
        <div class="max-w-7xl mx-auto">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0 animate-fade-in">
                    <h2 class="text-4xl font-extrabold text-white sm:text-5xl sm:tracking-tight lg:text-6xl">
                        Church Services
                    </h2>
                    <p class="mt-3 text-xl text-indigo-100 max-w-2xl">
                        Manage your weekly gatherings, special events, and service schedules in one beautiful place.
                    </p>
                </div>
                <div class="mt-6 flex md:mt-0 md:ml-4 animate-fade-in delay-100">
                    <a href="{{ route('services.calendar') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-white/10 hover:bg-white/20 backdrop-blur-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-600 focus:ring-white transition-all mr-3">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Calendar View
                    </a>
                    <a href="{{ route('services.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg shadow-lg text-base font-medium text-indigo-700 bg-white hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-indigo-600 focus:ring-white transition-all transform hover:scale-105">
                        <svg class="-ml-1 mr-3 h-5 w-5 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Service
                    </a>
                </div>
            </div>

            <!-- Stats Overview (Optional Placeholder) -->
            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-3 lg:grid-cols-4 animate-fade-in delay-200">
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden rounded-xl border border-white/20 p-5 shadow">
                    <dt class="truncate text-sm font-medium text-indigo-100">Total Services</dt>
                    <dd class="mt-1 text-3xl font-semibold text-white tracking-tight">{{ $services->total() }}</dd>
                </div>
                <div class="bg-white/10 backdrop-blur-lg overflow-hidden rounded-xl border border-white/20 p-5 shadow">
                    <dt class="truncate text-sm font-medium text-indigo-100">Active Recurring</dt>
                    <dd class="mt-1 text-3xl font-semibold text-white tracking-tight">{{ \App\Models\Service::active()->recurring()->count() }}</dd>
                </div>
                <!-- Add more stats if needed -->
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24">
        
        <!-- Filter Bar -->
        <div class="glass-panel rounded-2xl shadow-lg p-2 mb-8 animate-fade-in delay-300">
             <form action="{{ route('services.index') }}" method="GET" id="filter-form" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-2">
                
                <!-- Search -->
                <div class="relative flex-1 min-w-[300px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="search" name="search" value="{{ request('search') }}" 
                           class="block w-full pl-10 pr-3 py-2.5 border-none rounded-xl leading-5 bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:text-sm transition-colors hover:bg-white" 
                           placeholder="Search services...">
                </div>

                <!-- Filters Group -->
                <div class="flex flex-wrap items-center gap-3">
                    
                    <!-- Year -->
                    <select name="year" class="block w-28 pl-3 pr-10 py-2.5 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl bg-gray-50 hover:bg-white transition-colors cursor-pointer" onchange="this.form.submit()">
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                            <option value="{{ $y }}" {{ request('year', $year) == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>

                    <!-- Month -->
                    <select name="month" class="block w-32 pl-3 pr-10 py-2.5 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl bg-gray-50 hover:bg-white transition-colors cursor-pointer" onchange="this.form.submit()">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month', $month) == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Day -->
                    <select name="day_of_week" class="block w-32 pl-3 pr-10 py-2.5 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl bg-gray-50 hover:bg-white transition-colors cursor-pointer" onchange="this.form.submit()">
                        <option value="">Day</option>
                        @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $key => $val)
                            <option value="{{ $key }}" {{ request('day_of_week') === (string)$key ? 'selected' : '' }}>
                                {{ $val }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Status -->
                     <select name="status" class="block w-32 pl-3 pr-10 py-2.5 text-base border-gray-200 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-xl bg-gray-50 hover:bg-white transition-colors cursor-pointer" onchange="this.form.submit()">
                        <option value="">Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @if(request()->hasAny(['search', 'month', 'year', 'day_of_week', 'status']))
                        <a href="{{ route('services.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-xl text-red-700 bg-red-100 hover:bg-red-200 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-fade-in delay-100">
            @forelse($services as $service)
            <div class="service-card group relative flex flex-col bg-white rounded-2xl shadow-sm hover:shadow-xl overflow-hidden border border-gray-100 h-full">
                
                <!-- Status Banner -->
                <div class="absolute top-0 inset-x-0 h-1.5 {{ $service->status === 'active' ? 'bg-gradient-to-r from-emerald-400 to-teal-500' : 'bg-gray-300' }}"></div>

                <div class="p-6 flex-1 flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            @if($service->is_recurring)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-indigo-100 text-indigo-800 mb-2 uppercase tracking-wide">
                                    Weekly Input
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 mb-2 uppercase tracking-wide">
                                    One-time Event
                                </span>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                {{ $service->name }}
                            </h3>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-2 text-center min-w-[60px]">
                            @if($service->is_recurring)
                                <span class="block text-xs font-bold text-gray-500 uppercase">{{ substr($service->day_of_week_name, 0, 3) }}</span>
                                <span class="block text-lg font-extrabold text-gray-900">
                                    <svg class="w-6 h-6 mx-auto text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                </span>
                            @else
                                <span class="block text-xs font-bold text-gray-500 uppercase">{{ optional($service->date)->format('M') ?? 'N/A' }}</span>
                                <span class="block text-xl font-extrabold text-gray-900">{{ optional($service->date)->format('d') ?? '?' }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="space-y-3 mb-6">
                         @if($service->description)
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $service->description }}</p>
                        @endif

                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="h-4 w-4 mr-2.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}
                        </div>

                        @if($service->location)
                        <div class="flex items-center text-sm text-gray-600">
                            <svg class="h-4 w-4 mr-2.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $service->location }}
                        </div>
                        @endif
                        
                        @if($service->status === 'inactive')
                             <div class="flex items-center text-sm text-red-600 bg-red-50 p-2 rounded-lg">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                </svg>
                                Inactive
                            </div>
                        @endif
                    </div>

                    <!-- Capacity Bar -->
                    @if($service->capacity)
                    <div class="mt-auto pt-4 border-t border-gray-50">
                        <div class="flex justify-between text-xs font-semibold text-gray-500 mb-1">
                            <span>Capacity</span>
                            <span>{{ $service->capacity }} max</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 ml-0"> <!-- Fixed width -->
                             <!-- Placeholder width for capacity visualization -->
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                    @else
                    <div class="mt-auto pt-4 border-t border-gray-50"></div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between group-hover:bg-indigo-50 transition-colors">
                     <a href="{{ route('services.order-of-services.index', $service) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 flex items-center">
                        Manage Order
                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    
                    <div class="flex items-center space-x-3 opacity-60 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('services.edit', $service) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Edit">
                             <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('services.destroy', $service) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                 <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="text-center py-24 bg-white rounded-3xl shadow-sm border border-dashed border-gray-300">
                    <svg class="mx-auto h-20 w-20 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="mt-4 text-xl font-medium text-gray-900">No services found</h3>
                    <p class="mt-2 text-gray-500 max-w-sm mx-auto">Get started by creating your first service schedule or try adjusting your search filters.</p>
                    <div class="mt-8">
                        <a href="{{ route('services.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create New Service
                        </a>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-12">
            {{ $services->links() }}
        </div>
    </div>
</div>
@endsection
