@extends('layouts.admin')

@section('title', 'Worker Attendance & Allocations')
@section('header', 'Worker Attendance & Allocations')

@section('content')
<div class="space-y-6 fade-in">

    <!-- Filters -->
    <div class="glass-effect rounded-2xl p-6 mb-8">
        <form action="{{ route('attendance.workers') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" id="date" value="{{ request('date', $date->format('Y-m-d')) }}" 
                       class="w-full rounded-xl border-gray-300 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
            </div>
            <div class="flex-1">
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                <select name="service_id" id="service_id" class="w-full rounded-xl border-gray-300 focus:border-primary-500 focus:ring-primary-500 shadow-sm">
                    <option value="">All Services</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} ({{ \Carbon\Carbon::parse($service->start_time)->format('h:i A') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 font-medium transition-colors">
                    Filter Workers
                </button>
            </div>
        </form>
    </div>

    <!-- Workers List -->
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Worker Details</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Departments & Roles</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Check In Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white/50 divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover" 
                                         src="{{ $attendance->member->profile_photo_url }}" 
                                         alt="{{ $attendance->member->full_name }}">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $attendance->member->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attendance->member->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($attendance->member->departments as $deptPivot)
                                    @if($deptPivot->department)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $deptPivot->department->name }}
                                        </span>
                                    @endif
                                @endforeach
                                @foreach($attendance->member->roles as $role)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $attendance->service->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('h:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->is_present)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Present
                                </span>
                            @elseif($attendance->is_absent)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Absent
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($attendance->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">No workers checked in for this date/service.</p>
                                <p class="text-gray-400 text-sm mt-1">Check the general attendance to verify if check-ins have started.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
