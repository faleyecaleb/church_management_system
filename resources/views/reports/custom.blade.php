@php($filters = $filters ?? request()->all())
@extends('layouts.admin')
@section('title','Custom Reports')
@section('header','Custom Reports')
@section('content')
<div class="bg-white p-6 rounded-xl shadow-sm border">
    <form action="{{ route('reports.custom') }}" method="GET" class="space-y-3">
        <div>
            <label class="block text-sm font-medium">Metrics (comma separated)</label>
            <input type="text" name="metrics[]" class="w-full border rounded p-2" placeholder="membership,attendance" value="membership,attendance">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-sm font-medium">Start Date</label>
                <input type="date" name="start_date" class="w-full border rounded p-2" value="{{ now()->subMonth()->format('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium">End Date</label>
                <input type="date" name="end_date" class="w-full border rounded p-2" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium">Group By</label>
                <select name="group_by" class="w-full border rounded p-2">
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month" selected>Month</option>
                    <option value="year">Year</option>
                </select>
            </div>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Generate</button>
    </form>

    @isset($stats)
        <div class="mt-6">
            <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($stats, JSON_PRETTY_PRINT) }}</pre>
        </div>
    @endisset
</div>
@endsection