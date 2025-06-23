@extends('layouts.admin')

@section('title', 'View Service')
@section('header', 'Service Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">{{ $service->name }}</h2>
            <div class="space-x-2">
                @can('edit services')
                <a href="{{ route('services.edit', $service) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Service
                </a>
                @endcan
                <a href="{{ route('services.index') }}" class="text-gray-600 hover:text-gray-800">
                    Back to Services
                </a>
            </div>
        </div>

        <!-- Service Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Schedule</h3>
                    <p class="text-gray-600">
                        {{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$service->day_of_week] }}
                        {{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $service->is_recurring ? 'Recurring Weekly' : 'One-time Service' }}
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Location</h3>
                    <p class="text-gray-600">{{ $service->location }}</p>
                </div>

                @if($service->description)
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Description</h3>
                    <p class="text-gray-600">{{ $service->description }}</p>
                </div>
                @endif

                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Status</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $service->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($service->status) }}
                    </span>
                </div>

                @if($service->capacity)
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Capacity</h3>
                    <p class="text-gray-600">{{ $service->capacity }} people</p>
                </div>
                @endif

                @if($service->notes)
                <div>
                    <h3 class="text-lg font-semibold text-gray-700">Additional Notes</h3>
                    <p class="text-gray-600">{{ $service->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Recent Attendance -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4">Recent Attendance</h3>
                @if($service->attendances->count() > 0)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-4">
                            @foreach($service->attendances->take(5) as $attendance)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <img class="h-8 w-8 rounded-full" src="{{ $attendance->member->profile_photo_url }}" alt="{{ $attendance->member->full_name }}">
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-500">{{ substr($attendance->member->name, 0, 1) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $attendance->member->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $attendance->check_in_time->format('M j, Y g:i A') }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($attendance->check_in_method) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @if($service->attendances->count() > 5)
                            <div class="mt-4 text-center">
                                <a href="{{ route('attendance.service', ['service_id' => $service->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                    View all attendance records
                                </a>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-500 text-sm">No attendance records yet.</p>
                @endif
            </div>
        </div>

        <!-- QR Code Section -->
        @can('record attendance')
        <div class="border-t pt-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">QR Code Check-in</h3>
            <div class="flex items-center space-x-4">
                <a href="{{ route('attendance.show-qr-code', $service) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Generate QR Code
                </a>
                <span class="text-sm text-gray-500">Generate a QR code for quick check-in at this service</span>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection