@extends('layouts.admin')
@section('title','Attendance Reports')
@section('header','Attendance Reports')
@section('content')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Records', 'value' => $s['total_records'] ?? 0],
    ['label' => 'Days Recorded', 'value' => $s['total_services'] ?? 0],
    ['label' => 'Average/Day', 'value' => $s['average_attendance'] ?? 0],
    ['label' => 'Peak', 'value' => $s['highest_attendance'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-2">Trend (last 6 months)</h3>
    <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($s['attendance_trend'] ?? [], JSON_PRETTY_PRINT) }}</pre>
</div>
@endsection