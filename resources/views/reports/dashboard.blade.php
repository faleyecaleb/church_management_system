@extends('layouts.admin')
@section('title','Reports Dashboard')
@section('header','Reports Dashboard')
@section('content')
<div class="space-y-6">
    <div>
        @php
            $member = $stats['members'] ?? [];
            $attendance = $stats['attendance'] ?? [];
            $messages = $stats['messages'] ?? [];
            $donations = $stats['donations'] ?? [];
        @endphp
        @include('reports._partials.cards', ['cards' => [
            ['label' => 'Total Members', 'value' => $member['total_members'] ?? 0],
            ['label' => 'Active Members', 'value' => $member['active_members'] ?? 0],
            ['label' => 'Total Attendance Records', 'value' => $attendance['total_records'] ?? 0],
            ['label' => 'Messages (This Month)', 'value' => $messages['total_messages'] ?? 0],
        ]])
    </div>

    <div class="bg-white p-6 rounded-xl shadow-sm border">
        <h3 class="font-semibold mb-2">Raw Stats</h3>
        <pre class="text-xs bg-gray-50 p-3 rounded">{{ json_encode($stats, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
@endsection