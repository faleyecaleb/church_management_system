@extends('layouts.admin')

@section('title', 'Expense Reports')
@section('header', 'Expense Reports & Analytics')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
<style>
    .chart-container {
        position: relative;
        height: 300px;
        max-height: 300px;
        width: 100%;
        overflow: hidden;
    }
    
    .chart-container canvas {
        max-height: 300px !important;
        max-width: 100% !important;
    }
    
    .large-chart-container {
        position: relative;
        height: 400px;
        max-height: 400px;
        width: 100%;
        overflow: hidden;
    }
    
    .large-chart-container canvas {
        max-height: 400px !important;
        max-width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Date Range Filter -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('expenses.report') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate->format('Y-m-d') }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Expenses -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                    <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($byCategory->sum('total'), 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Number of Expenses -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Number of Expenses</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($byCategory->sum('count')) }}</p>
                </div>
            </div>
        </div>

        <!-- Average Expense -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Average Expense</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        ₦{{ $byCategory->sum('count') > 0 ? number_format($byCategory->sum('total') / $byCategory->sum('count'), 2) : '0.00' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Highest Category -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-purple-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Highest Category</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $byCategory->sortByDesc('total')->first()->category ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">₦{{ $byCategory->isNotEmpty() ? number_format($byCategory->sortByDesc('total')->first()->total, 2) : '0.00' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expenses by Category -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Category</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Expenses by Department -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Expenses by Department</h3>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Expense Trends</h3>
        <div class="large-chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Budget Utilization -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Utilization</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocated</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white/50">
                    @forelse($budgetUtilization as $budget)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $budget['name'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($budget['allocated'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($budget['used'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($budget['remaining'], 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $budget['utilization_rate'] }}%"></div>
                                    </div>
                                    <span class="{{ $budget['utilization_rate'] > 100 ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ number_format($budget['utilization_rate'], 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No budget data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed Breakdown Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category Breakdown -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Breakdown</h3>
            <div class="space-y-3">
                @forelse($byCategory as $category)
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-900">{{ $category->category }}</p>
                            <p class="text-sm text-gray-500">{{ $category->count }} expenses</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">₦{{ number_format($category->total, 2) }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $byCategory->sum('total') > 0 ? number_format(($category->total / $byCategory->sum('total')) * 100, 1) : 0 }}%
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No category data available.</p>
                @endforelse
            </div>
        </div>

        <!-- Department Breakdown -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Department Breakdown</h3>
            <div class="space-y-3">
                @forelse($byDepartment as $department)
                    <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-900">{{ $department->department }}</p>
                            <p class="text-sm text-gray-500">{{ $department->count }} expenses</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">₦{{ number_format($department->total, 2) }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $byDepartment->sum('total') > 0 ? number_format(($department->total / $byDepartment->sum('total')) * 100, 1) : 0 }}%
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No department data available.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category Chart
        const categoryElement = document.getElementById('categoryChart');
        if (categoryElement) {
            const categoryCtx = categoryElement.getContext('2d');
            new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json($byCategory->pluck('category')),
            datasets: [{
                data: @json($byCategory->pluck('total')),
                backgroundColor: [
                    '#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6',
                    '#EC4899', '#6B7280', '#14B8A6', '#F97316', '#84CC16'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₦' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
            });
        }

        // Department Chart
        const departmentElement = document.getElementById('departmentChart');
        if (departmentElement) {
            const departmentCtx = departmentElement.getContext('2d');
            new Chart(departmentCtx, {
        type: 'bar',
        data: {
            labels: @json($byDepartment->pluck('department')),
            datasets: [{
                label: 'Total Expenses',
                data: @json($byDepartment->pluck('total')),
                backgroundColor: '#EF4444'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₦' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
            });
        }

        // Monthly Chart
        const monthlyElement = document.getElementById('monthlyChart');
        if (monthlyElement) {
            const monthlyCtx = monthlyElement.getContext('2d');
            new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: @json($monthlyExpenses->pluck('month')),
            datasets: [{
                label: 'Monthly Expenses',
                data: @json($monthlyExpenses->pluck('total')),
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ₦' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
            });
        }
    });
</script>
@endpush
@endsection