@extends('layouts.admin')

@section('title', 'Budget Reports')
@section('header', 'Budget Reports & Analytics')

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
    <!-- Fiscal Year Filter -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('budgets.report') }}" method="GET" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="fiscal_year" class="block text-sm font-medium text-gray-700 mb-1">Fiscal Year</label>
                <select name="fiscal_year" id="fiscal_year" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for ($i = now()->year; $i >= now()->year - 5; $i--)
                        <option value="{{ $i }}" {{ $fiscalYear == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- Total Budget -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Budget</p>
                    <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($overallUtilization->total_budget ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Total Used -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Used</p>
                    <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($overallUtilization->total_used ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Utilization Rate -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-xl p-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Utilization Rate</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $overallUtilization->total_budget > 0 ? number_format(($overallUtilization->total_used / $overallUtilization->total_budget) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Budget by Category -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget by Category</h3>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Budget by Department -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget by Department</h3>
            <div class="chart-container">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison Chart -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Budget vs Actual Spending</h3>
        <div class="large-chart-container">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    <!-- Alerts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Overspent Budgets -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-red-600 mb-4">⚠️ Overspent Budgets</h3>
            @forelse($overspentBudgets as $budget)
                <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900">{{ $budget->name }}</p>
                        <p class="text-sm text-gray-500">{{ $budget->category }} - {{ $budget->department }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-red-600">₦{{ number_format($budget->used_amount - $budget->amount, 2) }} over</p>
                        <p class="text-xs text-gray-500">{{ number_format(($budget->used_amount / $budget->amount) * 100, 1) }}% used</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No overspent budgets found.</p>
            @endforelse
        </div>

        <!-- Low Utilization Budgets -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-yellow-600 mb-4">📊 Low Utilization Budgets</h3>
            @forelse($lowUtilizationBudgets as $budget)
                <div class="flex justify-between items-center py-2 border-b border-gray-200 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900">{{ $budget->name }}</p>
                        <p class="text-sm text-gray-500">{{ $budget->category }} - {{ $budget->department }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-yellow-600">{{ number_format(($budget->used_amount / $budget->amount) * 100, 1) }}% used</p>
                        <p class="text-xs text-gray-500">₦{{ number_format($budget->amount - $budget->used_amount, 2) }} remaining</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">All budgets have good utilization.</p>
            @endforelse
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
                data: @json($byCategory->pluck('total_budget')),
                backgroundColor: [
                    '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
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
                label: 'Budget',
                data: @json($byDepartment->pluck('total_budget')),
                backgroundColor: '#3B82F6'
            }, {
                label: 'Used',
                data: @json($byDepartment->pluck('total_used')),
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
            labels: @json($monthlyComparison->pluck('month')),
            datasets: [{
                label: 'Actual Spending',
                data: @json($monthlyComparison->pluck('actual_amount')),
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4
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