@extends('layouts.admin')

@section('title', 'Manage Attendance')
@section('header', 'Manage Service Attendance')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Service Info -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $service->name }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $service->day_of_week }} at {{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="showQrCode()" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    Show QR Code
                </button>
                <button onclick="checkOutAll()" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-xl hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Check Out All
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Attendees List -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 overflow-hidden hover:bg-white/90 transition-all duration-300">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Current Attendees</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="attendees-list">
                            @forelse ($attendees as $attendance)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $attendance->member->profile_photo_url }}" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $attendance->member->full_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $attendance->member->email }}</div>
                                            @if($attendance->member->departments->count() > 0)
                                                <div class="text-xs text-primary-600 font-medium">{{ $attendance->member->departments->pluck('department')->join(', ') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $attendance->check_in_time->format('g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ Str::title(str_replace('_', ' ', $attendance->check_in_method)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($attendance->check_out_time)
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
                                    @if (!$attendance->check_out_time)
                                        <button onclick="checkOutMember({{ $attendance->member->id }})" class="text-red-600 hover:text-red-900">
                                            Check Out
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No attendees yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $attendees->links() }}
                </div>
            </div>
        </div>

        <!-- Check-in Form -->
        <div class="space-y-6">
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Check In Members</h3>
                <div class="space-y-4">
                    <div>
                        <label for="member-search" class="block text-sm font-medium text-gray-700">Search Members</label>
                        <div class="mt-1">
                            <input type="text" id="member-search" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-xl" placeholder="Search by name or email...">
                        </div>
                    </div>
                    <div class="border rounded-xl p-4 max-h-96 overflow-y-auto" id="members-list">
                        @foreach ($members as $member)
                        <div class="flex items-center py-2 hover:bg-gray-50 rounded-lg px-2">
                            <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label class="ml-3 block text-sm font-medium text-gray-700">
                                {{ $member->full_name }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button onclick="checkInSelected()" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Check In Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qr-modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" aria-hidden="true">
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                <div class="absolute right-0 top-0 pr-4 pt-4">
                    <button type="button" onclick="hideQrCode()" class="rounded-md bg-white text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="text-center">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4">Service Check-in QR Code</h3>
                    <div class="flex justify-center" id="qr-code-container">
                        <!-- QR code will be inserted here -->
                    </div>
                    <p class="mt-4 text-sm text-gray-500" id="qr-expiry"></p>
                </div>
                <div class="mt-5 sm:mt-6">
                    <button type="button" onclick="regenerateQrCode()" class="inline-flex w-full justify-center rounded-xl bg-blue-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-600">
                        Regenerate QR Code
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const serviceId = {{ $service->id }};

// Search functionality
document.getElementById('member-search').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const membersList = document.getElementById('members-list');
    const members = membersList.getElementsByTagName('div');

    for (let member of members) {
        const label = member.getElementsByTagName('label')[0];
        const text = label.textContent.toLowerCase();
        member.style.display = text.includes(searchTerm) ? '' : 'none';
    }
});

// Check in selected members
async function checkInSelected() {
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
            body: JSON.stringify({ member_ids: selectedMembers })
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
async function checkOutMember(memberId) {
    if (!confirm('Are you sure you want to check out this member?')) return;

    try {
        const response = await fetch(`/attendance/${serviceId}/members/${memberId}/check-out`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message);
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
            }
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message);
            location.reload();
        } else {
            throw new Error(data.error || 'Failed to check out members');
        }
    } catch (error) {
        alert(error.message);
    }
}

// QR Code management
function showQrCode() {
    document.getElementById('qr-modal').classList.remove('hidden');
    regenerateQrCode();
}

function hideQrCode() {
    document.getElementById('qr-modal').classList.add('hidden');
}

async function regenerateQrCode() {
    try {
        const response = await fetch(`/attendance/${serviceId}/qr-code`);
        const data = await response.json();

        if (response.ok) {
            document.getElementById('qr-code-container').innerHTML = data.qr_code;
            document.getElementById('qr-expiry').textContent = `QR code expires at ${new Date(data.expires_at).toLocaleTimeString()}`;
        } else {
            throw new Error(data.error || 'Failed to generate QR code');
        }
    } catch (error) {
        alert(error.message);
    }
}

// Close modal when clicking outside
document.getElementById('qr-modal').addEventListener('click', function(e) {
    if (e.target === this) hideQrCode();
});
</script>
@endpush
@endsection