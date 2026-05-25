@extends('layouts.admin')

@section('title', 'Attendance Analytics')
@section('header', 'Punctuality & Regularity Analytics')

@section('content')
<div class="max-w-7xl mx-auto py-6 fade-in">

    <!-- Filters -->
    <div class="glass-effect rounded-2xl p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Member Engagement Analytics</h3>
            <p class="text-sm text-gray-500">Track who attends the most and who arrives earliest.</p>
        </div>
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <form action="{{ route('attendance.analytics') }}" method="GET" class="flex items-center space-x-2 flex-1 md:flex-none bg-white p-2 rounded-xl shadow-sm border border-gray-100">
                
                <select name="filter_type" class="rounded-lg border-transparent bg-gray-50 focus:ring-primary-500 focus:border-primary-500 text-sm py-2 font-medium" onchange="toggleFilters(this.value)">
                    <option value="timeframe" {{ $filterType == 'timeframe' ? 'selected' : '' }}>Rolling Days</option>
                    <option value="month" {{ $filterType == 'month' ? 'selected' : '' }}>Specific Month</option>
                </select>

                <!-- Timeframe Dropdown -->
                <select name="timeframe" id="timeframe_filter" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2 {{ $filterType == 'month' ? 'hidden' : '' }}">
                    <option value="30" {{ $timeframe == '30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="90" {{ $timeframe == '90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="365" {{ $timeframe == '365' ? 'selected' : '' }}>Last 365 Days</option>
                </select>

                <!-- Date Range Dropdowns -->
                <div id="month_filter" class="flex flex-wrap items-center space-x-2 {{ $filterType == 'timeframe' ? 'hidden' : '' }}">
                    <span class="text-xs font-medium text-gray-500 hidden sm:block">From:</span>
                    <select name="start_month" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $start_month == $m ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="start_year" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                        @for($y=date('Y'); $y>=2020; $y--)
                            <option value="{{ $y }}" {{ $start_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    
                    <span class="text-xs font-medium text-gray-500 ml-2 hidden sm:block">To:</span>
                    <select name="end_month" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $end_month == $m ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                    <select name="end_year" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                        @for($y=date('Y'); $y>=2020; $y--)
                            <option value="{{ $y }}" {{ $end_year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                
                <!-- Department and Group -->
                <select name="department" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                    <option value="">All Departments</option>
                    @foreach($formDepartments ?? [] as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>

                <select name="church_group" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2">
                    <option value="">All Groups</option>
                    @foreach($formGroups ?? [] as $group)
                        <option value="{{ $group }}" {{ request('church_group') == $group ? 'selected' : '' }}>{{ $group }}</option>
                    @endforeach
                </select>

                <button type="submit" class="p-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
            </form>

            <a href="{{ route('attendance.analytics.export', request()->all()) }}" class="flex items-center space-x-2 px-4 py-2.5 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 transition-colors shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="hidden sm:block">Export Excel</span>
            </a>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Regularity Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 glass-effect">
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Top 5 Regular Attendees</h4>
            <div class="relative h-64">
                <canvas id="regularChart"></canvas>
            </div>
        </div>

        <!-- Punctuality Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 glass-effect">
            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">Top 5 Punctual Members (Avg Mins Early)</h4>
            <div class="relative h-64">
                <canvas id="punctualChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Most Regular Members Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden glass-effect border-t-4 border-indigo-500">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                    Most Regular Members
                </h3>
                <span class="text-xs font-semibold bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">Top 10</span>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-gray-100">
                    @forelse($mostRegular as $member)
                    <li class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 font-bold text-gray-400 w-6 text-center">#{{ $loop->iteration }}</div>
                            <img class="h-10 w-10 rounded-full object-cover ml-2 border-2 border-indigo-100" src="{{ $member->profile_photo_url }}" alt="">
                            <div class="ml-4">
                                <p class="text-sm font-bold text-gray-900">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ $member->phone ?? 'No Phone' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-black text-indigo-600">{{ $member->attendance_count }}</span>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Services</p>
                        </div>
                    </li>
                    @empty
                    <li class="p-8 text-center text-gray-500">Not enough attendance data available for this timeframe.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Most Punctual Members Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden glass-effect border-t-4 border-green-500">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Most Punctual Members
                </h3>
                <span class="text-xs font-semibold bg-green-100 text-green-800 px-2 py-1 rounded-full">Top 10</span>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-gray-100">
                    @forelse($mostPunctual as $member)
                    <li class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 font-bold text-gray-400 w-6 text-center">#{{ $loop->iteration }}</div>
                            <img class="h-10 w-10 rounded-full object-cover ml-2 border-2 border-green-100" src="{{ $member->profile_photo_url }}" alt="">
                            <div class="ml-4">
                                <p class="text-sm font-bold text-gray-900">{{ $member->first_name }} {{ $member->last_name }}</p>
                                <p class="text-xs text-gray-500">{{ $member->total_attendances }} attendances recorded</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($member->avg_minutes_late < 0)
                                <span class="text-lg font-black text-green-600">{{ abs(round($member->avg_minutes_late)) }}m</span>
                                <p class="text-xs text-green-600 uppercase font-semibold">Early (Avg)</p>
                            @elseif($member->avg_minutes_late == 0)
                                <span class="text-lg font-black text-gray-600">On Time</span>
                                <p class="text-xs text-gray-500 uppercase font-semibold">Exact (Avg)</p>
                            @else
                                <span class="text-lg font-black text-red-500">{{ round($member->avg_minutes_late) }}m</span>
                                <p class="text-xs text-red-500 uppercase font-semibold">Late (Avg)</p>
                            @endif
                        </div>
                    </li>
                    @empty
                    <li class="p-8 text-center text-gray-500">Not enough check-in time data available. (Requires at least 2 check-ins).</li>
                    @endforelse
                </ul>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    function toggleFilters(val) {
        if(val === 'month') {
            document.getElementById('timeframe_filter').classList.add('hidden');
            document.getElementById('month_filter').classList.remove('hidden');
        } else {
            document.getElementById('timeframe_filter').classList.remove('hidden');
            document.getElementById('month_filter').classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const rawChartData = @json($chartData);

        // Regularity Chart (Bar)
        if(document.getElementById('regularChart') && rawChartData.regular_labels.length > 0) {
            new Chart(document.getElementById('regularChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: rawChartData.regular_labels,
                    datasets: [{
                        label: 'Total Attendances',
                        data: rawChartData.regular_data,
                        backgroundColor: 'rgba(99, 102, 241, 0.8)', // Indigo 500
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Punctuality Chart (Bar - negative values go down)
        // We flip the negative sign visually if you want 'Early' to point UP, but technically 'minutes late' where early is negative points DOWN.
        // Let's invert the data so EARLY points UP.
        if(document.getElementById('punctualChart') && rawChartData.punctual_labels.length > 0) {
            const invertedData = rawChartData.punctual_data.map(val => val * -1); // Early (negative) becomes positive for the chart height

            new Chart(document.getElementById('punctualChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: rawChartData.punctual_labels,
                    datasets: [{
                        label: 'Minutes Early (Avg)',
                        data: invertedData,
                        backgroundColor: invertedData.map(val => val >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)'), // Green if early, Red if late
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1500,
                        easing: 'easeOutElastic',
                        delay: 200
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let val = context.raw;
                                    if(val >= 0) return val + ' mins early';
                                    return Math.abs(val) + ' mins late';
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            title: { display: true, text: 'Minutes Early' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection

