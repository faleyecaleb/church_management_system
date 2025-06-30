@extends('layouts.admin')

@section('title', 'Pledge Reports')
@section('header', 'Pledge Reports & Analytics')

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
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Date Range Filter -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('pledges.report') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="w-48">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="defaulted" {{ $status == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Generate Report
                </button>
            </div>
        </form>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <!-- Total Pledges -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto bg-blue-500 rounded-xl p-3 w-fit mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Total Pledges</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalPledges) }}</p>
            </div>
        </div>

        <!-- Total Pledge Amount -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto bg-green-500 rounded-xl p-3 w-fit mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Total Pledged</p>
                <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($totalPledgeAmount, 2) }}</p>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto bg-purple-500 rounded-xl p-3 w-fit mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Total Paid</p>
                <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($totalPaidAmount, 2) }}</p>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto bg-red-500 rounded-xl p-3 w-fit mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Outstanding</p>
                <p class="text-2xl font-semibold text-gray-900">₦{{ number_format($totalOutstanding, 2) }}</p>
            </div>
        </div>

        <!-- Fulfillment Rate -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <div class="text-center">
                <div class="mx-auto bg-yellow-500 rounded-xl p-3 w-fit mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Fulfillment Rate</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($fulfillmentRate, 1) }}%</p>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Breakdown -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pledges by Status</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Monthly Trends -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trends</h3>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Campaign Breakdown -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Performance</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campaign</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pledges</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Paid</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fulfillment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white/50">
                    @forelse($campaignBreakdown as $campaign)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $campaign->campaign_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $campaign->count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($campaign->total_amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">₦{{ number_format($campaign->amount_paid, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="flex items-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $campaign->total_amount > 0 ? ($campaign->amount_paid / $campaign->total_amount) * 100 : 0 }}%"></div>
                                    </div>
                                    <span>{{ $campaign->total_amount > 0 ? number_format(($campaign->amount_paid / $campaign->total_amount) * 100, 1) : 0 }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No campaigns found for the selected period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Pledgers and Overdue -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Pledgers -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🏆 Top Pledgers</h3>
            @forelse($topPledgers as $pledger)
                <div class="flex justify-between items-center py-3 border-b border-gray-200 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900">{{ $pledger->member->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ number_format(($pledger->total_paid / $pledger->total_pledged) * 100, 1) }}% fulfilled</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">₦{{ number_format($pledger->total_pledged, 2) }}</p>
                        <p class="text-xs text-gray-500">₦{{ number_format($pledger->total_paid, 2) }} paid</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No pledgers found.</p>
            @endforelse
        </div>

        <!-- Overdue Pledges -->
        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <h3 class="text-lg font-semibold text-red-600 mb-4">⚠️ Overdue Pledges</h3>
            @forelse($overduePledges as $pledge)
                <div class="flex justify-between items-center py-3 border-b border-gray-200 last:border-b-0">
                    <div>
                        <p class="font-medium text-gray-900">{{ $pledge->member->full_name }}</p>
                        <p class="text-sm text-gray-500">{{ $pledge->campaign_name }}</p>
                        <p class="text-xs text-red-500">Due: {{ $pledge->end_date->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-red-600">₦{{ number_format($pledge->total_amount - $pledge->amount_paid, 2) }}</p>
                        <p class="text-xs text-gray-500">outstanding</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">No overdue pledges found.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status Chart
        const statusElement = document.getElementById('statusChart');
        if (statusElement) {
            const statusCtx = statusElement.getContext('2d');
            new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: @json($statusBreakdown->pluck('status')),
            datasets: [{
                data: @json($statusBreakdown->pluck('count')),
                backgroundColor: ['#10B981', '#3B82F6', '#EF4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
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
            labels: @json($monthlyTrends->pluck('month')),
            datasets: [{
                label: 'Pledged',
                data: @json($monthlyTrends->pluck('total_amount')),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4
            }, {
                label: 'Paid',
                data: @json($monthlyTrends->pluck('amount_paid')),
                borderColor: '#10B981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
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