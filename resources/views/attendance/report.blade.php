@extends('layouts.admin')

@section('title', 'Attendance Reports')
@section('header', 'Attendance Reports & Analytics')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 400px;
        max-height: 400px;
        width: 100%;
        overflow: hidden;
    }
    
    .chart-container canvas {
        max-height: 400px !important;
        max-width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Date Range Filter -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('attendance.report') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="w-48">
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                <select name="service_id" id="service_id" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Attendance -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Attendance</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalAttendance) }}</p>
                </div>
            </div>
        </div>

        <!-- Average Attendance -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Average Attendance</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($averageAttendance, 1) }}</p>
                </div>
            </div>
        </div>

        <!-- Peak Attendance -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Peak Attendance</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($peakAttendance) }}</p>
                </div>
            </div>
        </div>

        <!-- Growth Rate -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Growth Rate</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($growthRate, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Chart -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Attendance Trends</h3>
        <div class="chart-container">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="mt-6 bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Attendance Records</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance Count</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white/50">
                    @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $record->date }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $record->service_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $record->count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $record->check_in_method }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                No attendance records found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $attendanceRecords->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartElement = document.getElementById('attendanceChart');
        
        if (!chartElement) {
            console.error('Chart element not found');
            return;
        }

        const ctx = chartElement.getContext('2d');
        
        // Chart data with fallback
        const chartLabels = @json($chartData['labels'] ?? []);
        const chartData = @json($chartData['data'] ?? []);
        
        if (chartLabels.length === 0 || chartData.length === 0) {
            // Show no data message
            const container = chartElement.parentElement;
            container.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><p>No attendance data available for the selected period.</p></div>';
            return;
        }

        try {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Attendance',
                        data: chartData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    family: 'Inter'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1F2937',
                            bodyColor: '#1F2937',
                            borderColor: '#E5E7EB',
                            borderWidth: 1,
                            cornerRadius: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y + ' people';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#6B7280',
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#F3F4F6',
                                drawBorder: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                color: '#6B7280',
                                font: {
                                    size: 11,
                                    family: 'Inter'
                                },
                                callback: function(value) {
                                    return value + ' people';
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverBackgroundColor: 'rgb(59, 130, 246)'
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Error creating chart:', error);
            const container = chartElement.parentElement;
            container.innerHTML = '<div class="flex items-center justify-center h-full text-red-500"><p>Error loading chart. Please try again.</p></div>';
        }
    });
</script>
@endpush
@endsection