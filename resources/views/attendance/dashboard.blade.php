@extends('layouts.admin')

@section('title', 'Attendance Dashboard')
@section('header', 'Attendance Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Attendance Dashboard</h1>
                <p class="text-gray-600 mt-1">View and manage church attendance records.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('attendance.marking') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Mark Attendance
                </a>
                <a href="{{ route('attendance.bulk-marking') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Bulk Attendance (5000+ Members)
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Toggle Tabs -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
                <button onclick="switchTab('today')" id="today-tab" class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $showToday ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    Today's Records
                </button>
                <button onclick="switchTab('all')" id="all-tab" class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ !$showToday ? 'bg-white text-primary-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    All Records
                </button>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Weekly Reports
                </button>
                <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Attendance Patterns
                </button>
                <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Compare Periods
                </button>
                <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    Goals
                </button>
                <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 4.828A4 4 0 015.5 4H9v1H5.5a3 3 0 00-2.121.879l-.707.707A1 1 0 002 7.414V11H1V7.414a2 2 0 01.586-1.414l.707-.707A5 5 0 015.5 3H9a1 1 0 011 1v7a1 1 0 01-1 1H5.5a5 5 0 01-3.207-1.172z"></path>
                    </svg>
                    Reminders
                </button>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            @if($showToday && $currentService)
                Showing data for the most recent service: <span class="font-medium text-gray-900">{{ $currentService->name }}</span>
            @else
                Showing all attendance records for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
            @endif
        </div>
    </div>

    @if($currentService)
    <!-- Service Record Header -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $currentService->name }}</h2>
                <div class="flex items-center gap-4 mt-2 text-sm text-gray-600">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $currentService->start_time->format('g:i A') }}
                    </span>
                </div>
            </div>
            <div class="flex gap-3">
                <button class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Details
                </button>
                <button class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </button>
                <a href="{{ route('attendance.service') }}" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Mark Attendance
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Attendance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 text-sm font-medium">Total Marked</p>
                    <p class="text-3xl font-bold text-blue-900 mt-1">{{ $totalMarked }}</p>
                </div>
                <div class="bg-blue-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 text-sm font-medium">Present</p>
                    <p class="text-3xl font-bold text-green-900 mt-1">{{ $presentCount }}</p>
                    {{-- <p class="text-xs text-green-600 mt-1">(is_present: {{ $presentCount }})</p> --}}
                </div>
                <div class="bg-green-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-600 text-sm font-medium">Absent</p>
                    <p class="text-3xl font-bold text-red-900 mt-1">{{ $absentCount }}</p>
                    {{-- <p class="text-xs text-red-600 mt-1">(is_absent: {{ $absentCount }})</p> --}}
                </div>
                <div class="bg-red-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 border border-yellow-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-600 text-sm font-medium">Late</p>
                    <p class="text-3xl font-bold text-yellow-900 mt-1">{{ $late }}</p>
                </div>
                <div class="bg-yellow-500 rounded-full p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center text-sm text-gray-600 -mt-4">
        {{ $totalMarked }} members marked out of {{ $totalMembers }} active members.
    </div>

    <!-- Gender-based Attendance Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Male Attendance -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Male Attendance</h3>
                <div class="text-sm text-gray-600">
                    {{ $maleStats['total'] }} males / {{ $femaleStats['total'] }} females
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-4">{{ $maleStats['total'] }}</div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Present</span>
                    <span class="text-sm font-medium">{{ $maleStats['present'] }} ({{ $maleStats['total'] > 0 ? round(($maleStats['present'] / $maleStats['total']) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $maleStats['total'] > 0 ? ($maleStats['present'] / $maleStats['total']) * 100 : 0 }}%"></div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Absent</span>
                    <span class="text-sm font-medium">{{ $maleStats['absent'] }} ({{ $maleStats['total'] > 0 ? round(($maleStats['absent'] / ($maleStats['total'] + $maleStats['absent'])) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($maleStats['total'] + $maleStats['absent']) > 0 ? ($maleStats['absent'] / ($maleStats['total'] + $maleStats['absent'])) * 100 : 0 }}%"></div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Late</span>
                    <span class="text-sm font-medium">{{ $maleStats['late'] }} ({{ $maleStats['total'] > 0 ? round(($maleStats['late'] / $maleStats['total']) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $maleStats['total'] > 0 ? ($maleStats['late'] / $maleStats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>

        <!-- Female Attendance -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Female Attendance</h3>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-4">{{ $femaleStats['total'] }}</div>
            
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Present</span>
                    <span class="text-sm font-medium">{{ $femaleStats['present'] }} ({{ $femaleStats['total'] > 0 ? round(($femaleStats['present'] / $femaleStats['total']) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $femaleStats['total'] > 0 ? ($femaleStats['present'] / $femaleStats['total']) * 100 : 0 }}%"></div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Absent</span>
                    <span class="text-sm font-medium">{{ $femaleStats['absent'] }} ({{ $femaleStats['total'] > 0 ? round(($femaleStats['absent'] / ($femaleStats['total'] + $femaleStats['absent'])) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ ($femaleStats['total'] + $femaleStats['absent']) > 0 ? ($femaleStats['absent'] / ($femaleStats['total'] + $femaleStats['absent'])) * 100 : 0 }}%"></div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Late</span>
                    <span class="text-sm font-medium">{{ $femaleStats['late'] }} ({{ $femaleStats['total'] > 0 ? round(($femaleStats['late'] / $femaleStats['total']) * 100, 1) : 0 }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $femaleStats['total'] > 0 ? ($femaleStats['late'] / $femaleStats['total']) * 100 : 0 }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Attendance -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Department Attendance</h3>
            <button class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                View All Departments
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($departmentStats as $dept)
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-medium text-gray-900">{{ $dept['name'] }}</h4>
                    <span class="text-sm font-bold text-primary-600">{{ $dept['percentage'] }}%</span>
                </div>
                <div class="text-sm text-gray-600 mb-2">
                    {{ $dept['present'] }} of {{ $dept['total_members'] }} members
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-primary-500 h-2 rounded-full transition-all duration-300" style="width: {{ $dept['percentage'] }}%"></div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center text-gray-500 py-8">
                No department data available.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Attendance Activity -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Attendance Activity</h3>
            <a href="{{ route('attendance.service') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                View All Records
            </a>
        </div>
        
        <div class="space-y-4">
            @forelse($recentActivity as $activity)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 transition-colors">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-3 h-3 bg-primary-500 rounded-full"></div>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $activity['service_name'] }}</div>
                        <div class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($activity['date'])->format('M d, Y') }}</div>
                    </div>
                </div>
                <div class="flex items-center space-x-6 text-sm">
                    <div class="text-center">
                        <div class="font-medium text-gray-900">{{ $activity['total'] }}</div>
                        <div class="text-gray-500">Total</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-green-600">{{ $activity['present'] }}</div>
                        <div class="text-gray-500">Present</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-red-600">{{ $activity['absent'] }}</div>
                        <div class="text-gray-500">Absent</div>
                    </div>
                    <div class="text-center">
                        <div class="font-medium text-yellow-600">{{ $activity['late'] }}</div>
                        <div class="text-gray-500">Late</div>
                    </div>
                    <button class="text-primary-600 hover:text-primary-700 font-medium">
                        View
                    </button>
                </div>
            </div>
            @empty
            <div class="text-center text-gray-500 py-8">
                No recent attendance activity found.
            </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
    const todayTab = document.getElementById('today-tab');
    const allTab = document.getElementById('all-tab');
    
    if (tab === 'today') {
        todayTab.className = 'px-4 py-2 rounded-md text-sm font-medium transition-colors bg-white text-primary-600 shadow-sm';
        allTab.className = 'px-4 py-2 rounded-md text-sm font-medium transition-colors text-gray-500 hover:text-gray-700';
        
        // Redirect to today's view
        window.location.href = '{{ route("attendance.dashboard") }}?tab=today';
    } else {
        allTab.className = 'px-4 py-2 rounded-md text-sm font-medium transition-colors bg-white text-primary-600 shadow-sm';
        todayTab.className = 'px-4 py-2 rounded-md text-sm font-medium transition-colors text-gray-500 hover:text-gray-700';
        
        // Redirect to all records view
        window.location.href = '{{ route("attendance.dashboard") }}?tab=all';
    }
}
</script>
@endpush
@endsection