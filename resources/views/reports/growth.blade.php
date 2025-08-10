@extends('layouts.admin')
@section('title','Growth Reports')
@section('header','Growth & Engagement Reports')
@section('content')
@php($g = $growthStats ?? ($stats['growth'] ?? []))
@php($e = $engagementStats ?? ($stats['engagement'] ?? []))
@include('reports._partials.cards', ['cards' => [
    ['label' => 'YoY Growth %', 'value' => $g['year_over_year_growth'] ?? 0],
    ['label' => 'Retention %', 'value' => $g['retention_rate'] ?? 0],
    ['label' => 'Conversion %', 'value' => $g['conversion_rate'] ?? 0],
    ['label' => 'Attendance Engagement %', 'value' => $e['service_attendance'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-2">Details</h3>
    <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode(['growth' => $g, 'engagement' => $e], JSON_PRETTY_PRINT) }}</pre>
</div>
@endsection