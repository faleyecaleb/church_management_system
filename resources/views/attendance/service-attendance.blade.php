@extends('layouts.admin')

@section('title', 'Service Attendance')
@section('header', 'Manage Service Attendance')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Service Selection -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('attendance.service') }}" method="GET" class="flex items-end gap-4" id="attendanceForm">
            <div class="flex-1">
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Select Service</label>
                <select name="service_id" id="service_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    <option value="">Choose a service...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} ({{ $service->day_of_week }} at {{ $service->start_time->format('g:i A') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input type="date" name="date" id="date" value="{{ request('date', now()->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
            </div>
            <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                Load Attendance
            </button>
        </form>
    </div>

    @if(isset($selectedService))
    @dd($selectedService)

    <!-- Attendance Management -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $selectedService->name }}</h2>
                <p class="text-sm text-gray-500">{{ $selectedDate->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-3">
                <button onclick="showBulkCheckIn()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Bulk Check-in
                </button>
                <button onclick="checkOutAll()" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Check Out All
                </button>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="{{ $attendance->member->profile_photo_url }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $attendance->member->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $attendance->member->email }}</div>
                                    @if($attendance->member->department)
                                        <div class="text-xs text-primary-600 font-medium">{{ $attendance->member->department }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->check_in_time->format('g:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->check_out_time ? $attendance->check_out_time->format('g:i A') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($attendance->check_out_time)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Checked Out
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Present
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if(!$attendance->check_out_time)
                                <button onclick="checkOutMember({{ $attendance->id }})" class="text-red-600 hover:text-red-900">
                                    Check Out
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No attendance records for this service.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<!-- Bulk Check-in Modal -->
<div id="bulk-checkin-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" aria-hidden="true">
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div class="absolute right-0 top-0 pr-4 pt-4">
                    <button type="button" onclick="hideBulkCheckIn()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Bulk Check-in Members</h3>
                        <div class="mt-4">
                            <input type="text" id="member-search" placeholder="Search members..." 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 mb-4">
                            <div class="max-h-96 overflow-y-auto border rounded-md p-4" id="members-list">
                                @foreach($members as $member)
                                <div class="flex items-center py-2 hover:bg-gray-50">
                                    <input type="checkbox" name="member_ids[]" value="{{ $member->id }}"
                                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                    <label class="ml-3 block text-sm text-gray-700">{{ $member->full_name }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="bulkCheckIn()"
                            class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 sm:ml-3 sm:w-auto">
                        Check In Selected
                    </button>
                    <button type="button" onclick="hideBulkCheckIn()"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const serviceId = {{ $selectedService->id ?? 'null' }};
const selectedDate = '{{ $selectedDate->format('Y-m-d') ?? '' }}';

// Handle form submission
document.getElementById('attendanceForm').addEventListener('submit', function(e) {
    const serviceSelect = document.getElementById('service_id');
    if (!serviceSelect.value) {
        e.preventDefault();
        alert('Please select a service');
        return;
    }
});

// Search functionality
document.getElementById('member-search')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const membersList = document.getElementById('members-list');
    const members = membersList.getElementsByTagName('div');

    for (let member of members) {
        const label = member.getElementsByTagName('label')[0];
        const text = label.textContent.toLowerCase();
        member.style.display = text.includes(searchTerm) ? '' : 'none';
    }
});

// Modal management
function showBulkCheckIn() {
    document.getElementById('bulk-checkin-modal').classList.remove('hidden');
}

function hideBulkCheckIn() {
    document.getElementById('bulk-checkin-modal').classList.add('hidden');
}

// Bulk check-in
async function bulkCheckIn() {
    const selectedMembers = Array.from(document.querySelectorAll('input[name="member_ids[]"]:checked'))
        .map(checkbox => checkbox.value);

    if (selectedMembers.length === 0) {
        alert('Please select at least one member to check in.');
        return;
    }

    try {
        const response = await fetch(`/attendance/${serviceId}/check-in-multiple`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                member_ids: selectedMembers,
                date: selectedDate
            })
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message);
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to check in members');
        }
    } catch (error) {
        alert(error.message);
    }
}

// Check out member
async function checkOutMember(attendanceId) {
    if (!confirm('Are you sure you want to check out this member?')) return;

    try {
        const response = await fetch(`/attendance/${attendanceId}/check-out`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (response.ok) {
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to check out member');
        }
    } catch (error) {
        alert(error.message);
    }
}

// Check out all members
async function checkOutAll() {
    if (!confirm('Are you sure you want to check out all members?')) return;

    try {
        const response = await fetch(`/attendance/${serviceId}/check-out-all`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ date: selectedDate })
        });

        const data = await response.json();

        if (response.ok) {
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to check out all members');
        }
    } catch (error) {
        alert(error.message);
    }
}
</script>
@endpush
@endsection