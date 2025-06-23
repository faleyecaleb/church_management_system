@extends('layouts.admin')

@section('title', 'Mark Attendance')
@section('header', 'Attendance Marking')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Attendance Marking</h2>
            <p class="text-sm text-gray-500">Mark attendance for church members</p>
        </div>

        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('attendance.dashboard') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Attendance
            </a>
        </div>

        <div class="bg-primary-50 rounded-xl p-4 mb-6">
            <h3 class="text-lg font-medium text-primary-700 mb-2">Step 1 of 2</h3>
            <p class="text-primary-600">Select service, date and default status</p>
        </div>

        <form action="{{ route('attendance.marking.step1.process') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Service Selection -->
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service *</label>
                    <select name="service_id" id="service_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                        <option value="">Select service...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} ({{ $service->day_of_week }} - {{ $service->start_time->format('g:i A') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Attendance Date -->
                <div>
                    <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-1">Attendance Date *</label>
                    <input type="date" name="attendance_date" id="attendance_date" value="{{ now()->format('Y-m-d') }}" 
                        class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                </div>
            </div>

            <!-- Default Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Default Status *</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <input type="radio" name="default_status" id="status_present" value="present" class="peer sr-only" checked>
                        <label for="status_present" class="flex items-center p-4 bg-white border rounded-xl cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:bg-gray-50">
                            <div class="flex items-center justify-center w-6 h-6 bg-green-100 rounded-full mr-3">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Present</span>
                        </label>
                    </div>
                    
                    <div class="relative">
                        <input type="radio" name="default_status" id="status_absent" value="absent" class="peer sr-only">
                        <label for="status_absent" class="flex items-center p-4 bg-white border rounded-xl cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:bg-gray-50">
                            <div class="flex items-center justify-center w-6 h-6 bg-red-100 rounded-full mr-3">
                                <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Absent</span>
                        </label>
                    </div>
                    
                    <div class="relative">
                        <input type="radio" name="default_status" id="status_late" value="late" class="peer sr-only">
                        <label for="status_late" class="flex items-center p-4 bg-white border rounded-xl cursor-pointer peer-checked:border-primary-500 peer-checked:bg-primary-50 hover:bg-gray-50">
                            <div class="flex items-center justify-center w-6 h-6 bg-yellow-100 rounded-full mr-3">
                                <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Late</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Continue to Members
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection