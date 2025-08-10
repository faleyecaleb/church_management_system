@extends('layouts.admin')
@section('title','Attendance Reports')
@section('header','Attendance Reports')
@section('content')
@include('reports._partials/filters')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Records', 'value' => $s['total_records'] ?? 0],
    ['label' => 'Days Recorded', 'value' => $s['total_services'] ?? 0],
    ['label' => 'Average/Day', 'value' => $s['average_attendance'] ?? 0],
    ['label' => 'Peak', 'value' => $s['highest_attendance'] ?? 0],
]])

<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">Attendance Trend</h3>
    <div class="h-64">
        <canvas id="attendanceTrendChart"></canvas>
    </div>
</div>

<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-4">By Service</h3>
    <div class="h-64">
        <canvas id="attendanceServiceChart"></canvas>
    </div>
</div>

<form method="POST" action="{{ route('reports.export') }}" class="mt-4"> @csrf
    <input type="hidden" name="type" value="attendance">
    <input type="hidden" name="format" value="csv">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Export CSV</button>
</form>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  const trendData = @json($s['attendance_trend'] ?? []);
  const trendLabels = trendData.map(i => i.day);
  const trendValues = trendData.map(i => i.total);
  const trendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
  new Chart(trendCtx, {
    type: 'line',
    data: { labels: trendLabels, datasets: [{ label: 'Attendance', data: trendValues, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.1)', fill: true, tension: 0.3 }] },
    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 0 }, plugins: { legend: { display: false } }, scales: { x: { ticks: { maxTicksLimit: 10 } }, y: { beginAtZero: true } } }
  });

  const serviceData = @json(($s['service_comparison'] ?? []));
  const serviceLabels = Object.keys(serviceData);
  const serviceValues = Object.values(serviceData);
  const serviceCtx = document.getElementById('attendanceServiceChart').getContext('2d');
  new Chart(serviceCtx, {
    type: 'bar',
    data: { labels: serviceLabels, datasets: [{ label: 'Count', data: serviceValues, backgroundColor: '#10b981' }] },
    options: { responsive: true, maintainAspectRatio: false, animation: { duration: 0 }, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
})();
</script>
@endpush
@endsection
