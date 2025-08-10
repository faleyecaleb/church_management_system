@extends('layouts.admin')
@section('title','Growth Reports')
@section('header','Growth & Engagement Reports')
@section('content')
@include('reports._partials/filters')
@php($g = $growthStats ?? ($stats['growth'] ?? []))
@php($e = $engagementStats ?? ($stats['engagement'] ?? []))
@include('reports._partials.cards', ['cards' => [
    ['label' => 'YoY Growth %', 'value' => $g['year_over_year_growth'] ?? 0],
    ['label' => 'Retention %', 'value' => $g['retention_rate'] ?? 0],
    ['label' => 'Conversion %', 'value' => $g['conversion_rate'] ?? 0],
    ['label' => 'Attendance Engagement %', 'value' => $e['service_attendance'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">Monthly Growth (This Year)</h3>
    <div class="h-64"><canvas id="monthlyGrowthChart"></canvas></div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  const monthly = @json($g['monthly_growth_rate'] ?? []);
  const growthEl = document.getElementById('monthlyGrowthChart').getContext('2d');
  if (Object.keys(monthly).length) {
    new Chart(growthEl, { type:'bar', data:{ labels: Object.keys(monthly), datasets:[{ label:'New Members', data: Object.values(monthly), backgroundColor:'#a855f7' }] }, options:{ responsive:true, maintainAspectRatio:false, animation:{duration:0}, plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true} } } });
  } else {
    document.getElementById('monthlyGrowthChart').parentElement.innerHTML = '<div class="text-sm text-gray-500">No data to display</div>';
  }
})();
</script>
@endpush

<form method="POST" action="{{ route('reports.export') }}" class="mt-4"> @csrf
    <input type="hidden" name="type" value="growth">
    <input type="hidden" name="format" value="csv">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Export CSV</button>
</form>
@endsection