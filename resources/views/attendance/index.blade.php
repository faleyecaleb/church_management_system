@extends('layouts.admin')

@section('title', 'Attendance')
@section('header', 'Attendance Records')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Attendance Records</h2>
            <div class="space-x-2">
                @can('attendance.create')
                <a href="{{ route('attendance.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Record Attendance
                </a>
                @endcan
            </div>
        </div>

        <!-- Services Quick Access -->
        @can('attendance.create')
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3">Today's Services</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @forelse($services->where('day_of_week', now()->dayOfWeek) as $service)
                    <div class="border rounded-lg p-4 bg-gray-50">
                        <h4 class="font-medium">{{ $service->name }}</h4>
                        <p class="text-sm text-gray-600">{{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}</p>
                        <div class="mt-3 space-x-2">
                            <a href="{{ route('attendance.show-qr', $service) }}" class="inline-block bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 transition-colors">
                                QR Check-in
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No services scheduled for today.</p>
                @endforelse
            </div>
        </div>
        @endcan

        <!-- Filter Form -->
        <form action="{{ route('attendance.index') }}" method="GET" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                    <select name="service_id" id="service_id" class="w-full rounded-md border-gray-300">
                        <option value="">All Services</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="check_in_method" class="block text-sm font-medium text-gray-700 mb-1">Check-in Method</label>
                    <select name="check_in_method" id="check_in_method" class="w-full rounded-md border-gray-300">
                        <option value="">All Methods</option>
                        <option value="manual" {{ request('check_in_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="qr" {{ request('check_in_method') == 'qr' ? 'selected' : '' }}>QR Code</option>
                        <option value="mobile" {{ request('check_in_method') == 'mobile' ? 'selected' : '' }}>Mobile App</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Apply Filters
                </button>
                <a href="{{ route('attendance.index') }}" class="ml-2 text-gray-600 hover:text-gray-800">
                    Clear Filters
                </a>
            </div>
        </form>

        <!-- Attendance Stats -->
        <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-700">Total Check-ins</h4>
                <p class="text-2xl font-bold text-blue-800" id="total-checkins">Loading...</p>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-green-700">QR Check-ins</h4>
                <p class="text-2xl font-bold text-green-800" id="qr-checkins">Loading...</p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-purple-700">Mobile Check-ins</h4>
                <p class="text-2xl font-bold text-purple-800" id="mobile-checkins">Loading...</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700">Manual Check-ins</h4>
                <p class="text-2xl font-bold text-gray-800" id="manual-checkins">Loading...</p>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checked By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($attendance->member->profile_photo)
                                        <img src="{{ asset('storage/' . $attendance->member->profile_photo) }}" alt="Profile Photo" class="h-8 w-8 rounded-full mr-2">
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $attendance->member->first_name }} {{ $attendance->member->last_name }}
                                        </div>
                                        @if($attendance->member->department)
                                            <div class="text-xs text-primary-600 font-medium">{{ $attendance->member->department }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->service->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_in_time->format('M d, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ ucfirst($attendance->check_in_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->check_in_location ?: 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $attendance->checkedInBy->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @can('attendance.update')
                                <a href="{{ route('attendance.edit', $attendance) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                @endcan
                                @can('attendance.delete')
                                <form action="{{ route('attendance.destroy', $attendance) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this attendance record?')">
                                        Delete
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $attendances->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadAttendanceStats() {
    const params = new URLSearchParams(window.location.search);
    fetch(`/attendance/stats?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('total-checkins').textContent = data.total;
            document.getElementById('qr-checkins').textContent = data.by_method.qr || 0;
            document.getElementById('mobile-checkins').textContent = data.by_method.mobile || 0;
            document.getElementById('manual-checkins').textContent = data.by_method.manual || 0;
        })
        .catch(error => {
            console.error('Error loading attendance stats:', error);
        });
}

document.addEventListener('DOMContentLoaded', loadAttendanceStats);
</script>
@endpush
@endsection