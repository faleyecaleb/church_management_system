@extends('layouts.admin')
@section('title','Communication Reports')
@section('header','Communication Reports')
@section('content')
@include('reports._partials/filters')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Messages (This Month)', 'value' => $s['total_messages'] ?? 0],
    ['label' => 'Engagement %', 'value' => $s['engagement_rate'] ?? 0],
    ['label' => 'Delivered', 'value' => $s['delivery_stats']['delivered'] ?? 0],
    ['label' => 'Failed', 'value' => $s['delivery_stats']['failed'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">By Type</h3>
    <div class="h-64"><canvas id="messagesByTypeChart"></canvas></div>
</div>
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">Delivery Status</h3>
    <div class="h-64"><canvas id="messagesDeliveryChart"></canvas></div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const byType = @json($s['by_type'] ?? []);
new Chart(document.getElementById('messagesByTypeChart'), { type:'doughnut', data:{ labels: Object.keys(byType), datasets:[{ data: Object.values(byType), backgroundColor:['#eab308','#10b981','#6366f1'] }] }, options:{ responsive:true, maintainAspectRatio:false } });

const del = @json($s['delivery_stats'] ?? []);
new Chart(document.getElementById('messagesDeliveryChart'), { type:'bar', data:{ labels: Object.keys(del), datasets:[{ label:'Count', data: Object.values(del), backgroundColor:'#f97316' }] }, options:{ responsive:true, maintainAspectRatio:false } });
</script>
@endpush

<form method="POST" action="{{ route('reports.export') }}" class="mt-4"> @csrf
    <input type="hidden" name="type" value="communication">
    <input type="hidden" name="format" value="csv">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Export CSV</button>
</form>
@endsection