@extends('layouts.admin')
@section('title','Financial Reports')
@section('header','Financial Reports')
@section('content')
@php($s = $stats ?? [])
@include('reports._partials.cards', ['cards' => [
    ['label' => 'Total Amount (This Month)', 'value' => $s['total_amount'] ?? 0],
    ['label' => 'Average Donation', 'value' => $s['average_donation'] ?? 0],
    ['label' => 'Donor Count', 'value' => $s['donor_count'] ?? 0],
]])
<div class="bg-white p-6 rounded-xl shadow-sm border mt-4">
    <h3 class="font-semibold mb-2">Details</h3>
    <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($s, JSON_PRETTY_PRINT) }}</pre>
</div>
@endsection