@extends('layouts.admin')
@section('title','Membership Reports')
@section('header','Membership Reports')
@section('content')
@include('reports._partials/filters')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Members', 'value' => $s['total_members'] ?? 0],
    ['label' => 'Active', 'value' => $s['active_members'] ?? 0],
    ['label' => 'Inactive', 'value' => $s['inactive_members'] ?? 0],
    ['label' => 'New This Month', 'value' => $s['new_members_this_month'] ?? 0],
]])
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-semibold mb-4">Age Groups</h3>
        <div class="h-64">
            <canvas id="ageGroupChart"></canvas>
        </div>
    </div>
    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-semibold mb-4">Gender Distribution</h3>
        <div class="h-64">
            <canvas id="genderChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ages = @json($s['demographics']['age_groups'] ?? []);
new Chart(document.getElementById('ageGroupChart'), { type:'bar', data:{ labels: Object.keys(ages), datasets:[{ label:'Members', data: Object.values(ages), backgroundColor:'#06b6d4' }] }, options:{ responsive:true, maintainAspectRatio:false } });

const genders = @json($s['demographics']['gender_distribution'] ?? []);
new Chart(document.getElementById('genderChart'), { type:'pie', data:{ labels: Object.keys(genders), datasets:[{ data: Object.values(genders), backgroundColor:['#60a5fa','#f472b6','#d1d5db'] }] }, options:{ responsive:true, maintainAspectRatio:false } });
</script>
@endpush

<form method="POST" action="{{ route('reports.export') }}" class="mt-4"> @csrf
    <input type="hidden" name="type" value="membership">
    <input type="hidden" name="format" value="csv">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Export CSV</button>
</form>
@endsection