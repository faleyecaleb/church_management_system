@extends('layouts.admin')
@section('title','Membership Reports')
@section('header','Membership Reports')
@section('content')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Members', 'value' => $s['total_members'] ?? 0],
    ['label' => 'Active', 'value' => $s['active_members'] ?? 0],
    ['label' => 'Inactive', 'value' => $s['inactive_members'] ?? 0],
    ['label' => 'New This Month', 'value' => $s['new_members_this_month'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-2">Details</h3>
    <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($s, JSON_PRETTY_PRINT) }}</pre>
</div>
@endsection