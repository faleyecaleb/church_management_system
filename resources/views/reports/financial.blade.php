@extends('layouts.admin')
@section('title','Financial Reports')
@section('header','Financial Reports')
@section('content')
@include('reports._partials/filters')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Amount (This Month)', 'value' => $s['total_amount'] ?? 0],
    ['label' => 'Average Donation', 'value' => $s['average_donation'] ?? 0],
    ['label' => 'Donor Count', 'value' => $s['donor_count'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">Donations Trend</h3>
    <div class="h-64"><canvas id="donationsTrendChart"></canvas></div>
</div>
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">By Campaign</h3>
    <div class="h-64"><canvas id="donationsCampaignChart"></canvas></div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const dTrend = @json($s['trend'] ?? []);
new Chart(document.getElementById('donationsTrendChart'), { type:'line', data:{ labels: dTrend.map(i=>i.day), datasets:[{ label:'Amount', data: dTrend.map(i=>i.total), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.1)', fill:true, tension:0.3 }] }, options:{ responsive:true, maintainAspectRatio:false } });

const byCat = @json($s['campaign_performance'] ?? []);
new Chart(document.getElementById('donationsCampaignChart'), { type:'bar', data:{ labels: byCat.map(i=>i.campaign), datasets:[{ label:'Total', data: byCat.map(i=>i.total), backgroundColor:'#3b82f6' }] }, options:{ responsive:true, maintainAspectRatio:false } });
</script>
@endpush

<form method="POST" action="{{ route('reports.export') }}" class="mt-4"> @csrf
    <input type="hidden" name="type" value="financial">
    <input type="hidden" name="format" value="csv">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Export CSV</button>
</form>
@endsection