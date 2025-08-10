@extends('layouts.admin')
@section('title','Communication Reports')
@section('header','Communication Reports')
@section('content')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Messages (This Month)', 'value' => $s['total_messages'] ?? 0],
    ['label' => 'Engagement %', 'value' => $s['engagement_rate'] ?? 0],
    ['label' => 'Delivered', 'value' => $s['delivery_stats']['delivered'] ?? 0],
    ['label' => 'Failed', 'value' => $s['delivery_stats']['failed'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-2">By Type</h3>
    <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($s['by_type'] ?? [], JSON_PRETTY_PRINT) }}</pre>
</div>
@endsection