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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="font-semibold mb-4">Attendance Trend</h3>
            <canvas id="dashAttendanceChart" height="140"></canvas>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <h3 class="font-semibold mb-4">Donations Trend</h3>
            <canvas id="dashDonationsChart" height="140"></canvas>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border lg:col-span-2">
            <h3 class="font-semibold mb-4">Messages by Type</h3>
            <canvas id="dashMessagesChart" height="120"></canvas>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      const aTrend = @json(($attendance['attendance_trend'] ?? []));
      new Chart(document.getElementById('dashAttendanceChart'), { type: 'line', data: { labels: aTrend.map(i=>i.day), datasets: [{ label: 'Attendance', data: aTrend.map(i=>i.total), borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,0.1)', fill:true, tension:0.3 }] }, options:{responsive:true, maintainAspectRatio:false}});

      const dTrend = @json(($donations['trend'] ?? []));
      new Chart(document.getElementById('dashDonationsChart'), { type: 'line', data: { labels: dTrend.map(i=>i.day), datasets: [{ label: 'Donations', data: dTrend.map(i=>i.total), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,0.1)', fill:true, tension:0.3 }] }, options:{responsive:true, maintainAspectRatio:false}});

      const msg = @json(($messages ?? []));
      const msgData = { labels: ['SMS','Prayer','Internal'], datasets:[{ data: [msg.by_type?.sms||0, msg.by_type?.prayer||0, msg.by_type?.internal||0], backgroundColor:['#f59e0b','#ef4444','#6366f1'] }] };
      new Chart(document.getElementById('dashMessagesChart'), { type: 'doughnut', data: msgData, options:{responsive:true, maintainAspectRatio:false}});
    </script>
    @endpush
</div>
@endsection