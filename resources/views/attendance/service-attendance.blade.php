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

    <!-- Attendance Management -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $selectedService->name }}</h2>
                <p class="text-sm text-gray-500">{{ $selectedDate->format('l, F j, Y') }}</p>
            </div>
            <div class="flex gap-3">
                <h3 class="text-sm text-gray-600">Select a member below to mark their attendance</h3>
            </div>
        </div>

        <!-- Attendance List -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50" id="attendance-row-{{ $attendance->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="{{ $attendance->member->profile_photo_url }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $attendance->member->full_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->member->email }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->member->department ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attendance->is_present ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $attendance->is_present ? 'Present' : 'Absent' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2" id="action-buttons-{{ $attendance->id }}">
                                <button onclick="markAttendance({{ $attendance->id }}, true)" class="px-3 py-1 bg-green-100 text-green-800 rounded-full hover:bg-green-200 transition-colors">
                                    Present
                                </button>
                                <button onclick="markAttendance({{ $attendance->id }}, false)" class="px-3 py-1 bg-red-100 text-red-800 rounded-full hover:bg-red-200 transition-colors">
                                    Absent
                                </button>
                                <button onclick="deleteAttendance({{ $attendance->id }})" class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200 transition-colors">
                                    Delete
                                </button>
                            </div>
                            <div id="loading-{{ $attendance->id }}" class="hidden">
                                <svg class="animate-spin h-5 w-5 mx-auto text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
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



@push('scripts')
<script>
const serviceId = {{ $selectedService->id ?? 'null' }};
const selectedDate = '{{ $selectedDate->format('Y-m-d') ?? '' }}';
const csrfToken = '{{ csrf_token() }}';

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
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const nameCell = row.querySelector('td:first-child');
        if (nameCell) {
            const name = nameCell.textContent.toLowerCase();
            row.style.display = name.includes(searchTerm) ? '' : 'none';
        }
    });
});

// Show loading state
function showLoading(attendanceId) {
    const actionButtons = document.getElementById(`action-buttons-${attendanceId}`);
    const loading = document.getElementById(`loading-${attendanceId}`);
    actionButtons.classList.add('hidden');
    loading.classList.remove('hidden');
}

// Hide loading state
function hideLoading(attendanceId) {
    const actionButtons = document.getElementById(`action-buttons-${attendanceId}`);
    const loading = document.getElementById(`loading-${attendanceId}`);
    actionButtons.classList.remove('hidden');
    loading.classList.add('hidden');
}

// Show toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} transition-opacity duration-300`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Mark attendance
async function markAttendance(attendanceId, isPresent) {
    showLoading(attendanceId);
    
    try {
        const response = await fetch(`/attendance/${attendanceId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                is_present: isPresent,
                is_absent: !isPresent
            })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to update attendance');
        }

        // Update the UI
        const statusBadge = document.querySelector(`#attendance-row-${attendanceId} td:nth-child(4) span`);
        statusBadge.textContent = isPresent ? 'Present' : 'Absent';
        statusBadge.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
            isPresent ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
        }`;
        
        showToast('Attendance updated successfully');
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        hideLoading(attendanceId);
    }
}

// Delete attendance
async function deleteAttendance(attendanceId) {
    if (!confirm('Are you sure you want to delete this attendance record?')) {
        return;
    }
    
    showLoading(attendanceId);
    
    try {
        const response = await fetch(`/attendance/${attendanceId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to delete attendance record');
        }

        // Remove the row from the table
        const row = document.querySelector(`#attendance-row-${attendanceId}`);
        row.remove();
        
        // Check if there are any remaining rows
        const tbody = row.parentElement;
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No attendance records for this service.
                    </td>
                </tr>
            `;
        }
        
        showToast('Attendance record deleted successfully');
    } catch (error) {
        showToast(error.message, 'error');
    } finally {
        hideLoading(attendanceId);
    }
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
// Edit attendance
async function editAttendance(attendanceId) {
    const status = prompt('Update attendance status (present/absent):', '');
    if (!status || !['present', 'absent'].includes(status.toLowerCase())) {
        alert('Invalid status. Please enter either "present" or "absent".');
        return;
    }

    try {
        const response = await fetch(`/attendance/${attendanceId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                is_present: status.toLowerCase() === 'present' ? 1 : 0,
                is_absent: status.toLowerCase() === 'absent' ? 1 : 0
            })
        });

        const data = await response.json();

        if (response.ok) {
            // Update the UI
            const row = document.getElementById(`attendance-row-${attendanceId}`);
            const statusCell = row.querySelector('td:nth-child(4) span');
            
            statusCell.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                status.toLowerCase() === 'present' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            }`;
            statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            
            alert('Attendance updated successfully');
        } else {
            throw new Error(data.error || 'Failed to update attendance');
        }
    } catch (error) {
        alert(error.message);
    }
}

// Delete attendance
async function deleteAttendance(attendanceId) {
    if (!confirm('Are you sure you want to delete this attendance record?')) return;

    try {
        const response = await fetch(`/attendance/${attendanceId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        });

        const data = await response.json();

        if (response.ok) {
            // Remove the row from the table
            const row = document.getElementById(`attendance-row-${attendanceId}`);
            row.remove();
            
            // Check if there are any remaining rows
            const tbody = document.querySelector('tbody');
            if (tbody.children.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No attendance records for this service.
                        </td>
                    </tr>
                `;
            }
            
            alert('Attendance record deleted successfully');
        } else {
            throw new Error(data.error || 'Failed to delete attendance');
        }
    } catch (error) {
        alert(error.message);
    }
}
</script>
@endpush
@endsection