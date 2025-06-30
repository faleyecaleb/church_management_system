@extends('layouts.admin')

@section('title', 'Order of Service Management')
@section('header', 'Order of Service Management')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Order of Service Management</h1>
                <p class="mt-1 text-sm text-gray-600">Manage the order of service for all your church services</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('services.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Service
                </a>
            </div>
        </div>
    </div>

    <!-- Services Grid -->
    @if($services->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <!-- Service Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $service->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $service->day_of_week_name }}s at {{ $service->start_time->format('h:i A') }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($service->status) }}
                            </span>
                        </div>
                        
                        @if($service->description)
                            <p class="mt-2 text-sm text-gray-500">{{ Str::limit($service->description, 100) }}</p>
                        @endif
                    </div>

                    <!-- Order of Service Stats -->
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $service->order_count }}</div>
                                <div class="text-xs text-gray-500">Program Items</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    {{ $service->total_duration > 0 ? $service->total_duration . 'm' : '-' }}
                                </div>
                                <div class="text-xs text-gray-500">Total Duration</div>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($service->order_count > 0)
                            <div class="flex items-center justify-center mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Order Configured
                                </span>
                            </div>
                        @else
                            <div class="flex items-center justify-center mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    No Order Set
                                </span>
                            </div>
                        @endif

                        <!-- Recent Items Preview -->
                        @if($service->orderOfServices->count() > 0)
                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Recent Items:</h4>
                                <div class="space-y-1">
                                    @foreach($service->orderOfServices->take(3) as $item)
                                        <div class="flex items-center text-xs text-gray-600">
                                            <span class="w-4 h-4 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-medium mr-2">
                                                {{ $item->order }}
                                            </span>
                                            {{ Str::limit($item->program, 25) }}
                                        </div>
                                    @endforeach
                                    @if($service->orderOfServices->count() > 3)
                                        <div class="text-xs text-gray-400 ml-6">
                                            +{{ $service->orderOfServices->count() - 3 }} more items
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200 flex justify-between space-x-2">
                        <a href="{{ route('services.order-of-services.index', $service->id) }}" 
                           class="flex-1 inline-flex items-center justify-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Manage Order
                        </a>
                        
                        @if($service->orderOfServices->count() > 0)
                            <a href="{{ route('services.order-of-services.print', $service->id) }}" 
                               class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 shadow-lg">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No services found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating your first church service.</p>
                <div class="mt-6">
                    <a href="{{ route('services.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Create First Service
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Stats Summary -->
    @if($services->count() > 0)
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $services->count() }}</div>
                    <div class="text-sm text-gray-500">Total Services</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $services->where('order_count', '>', 0)->count() }}</div>
                    <div class="text-sm text-gray-500">With Order Set</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $services->sum('order_count') }}</div>
                    <div class="text-sm text-gray-500">Total Program Items</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $services->sum('total_duration') > 0 ? $services->sum('total_duration') . 'm' : '-' }}
                    </div>
                    <div class="text-sm text-gray-500">Total Duration</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection