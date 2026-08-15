@extends('layouts.admin')

@section('title', 'Service Attendance')
@section('header', 'Manage Service Attendance')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Service Selection -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('attendance.service') }}" method="GET" class="flex flex-wrap items-end gap-4" id="attendanceForm">
            <div class="w-full md:w-auto">
                <label for="filter_year" class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select id="filter_year" name="year" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @php $currentYear = date('Y'); @endphp
                    @for($y = $currentYear - 2; $y <= $currentYear + 1; $y++)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="w-full md:w-auto">
                <label for="filter_month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select id="filter_month" name="month" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="flex-1 min-w-[250px]">
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Select Service</label>
                <select name="service_id" id="service_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" required>
                    <option value="">Choose a service...</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} ({{ $service->is_recurring ? $service->day_of_week_name . 's' : (optional($service->start_date)->format('M j, Y') ?? 'One-time') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors w-full md:w-auto">
                Load Attendance
            </button>
        </form>
    </div>

    @if(isset($selectedService))

    <!-- Attendance Management -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">{{ $selectedService->name }}</h2>
                <p class="text-sm text-gray-500">Attendance records for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="px-4 py-2 bg-green-50 border border-green-200 rounded-xl flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                    <span class="text-xs font-semibold text-green-700">Total Present: <span class="text-sm font-bold text-green-800 ml-1">{{ $totalPresent }}</span></span>
                </div>
                <div class="px-4 py-2 bg-red-50 border border-red-200 rounded-xl flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                    <span class="text-xs font-semibold text-red-700">Total Absent: <span class="text-sm font-bold text-red-800 ml-1">{{ $totalAbsent }}</span></span>
                </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                            {{ $attendance->member->department_list ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $attendance->attendance_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($attendance->check_in_method === 'qr')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800" title="Scanned via Mobile App">
                                    Mobile (QR)
                                </span>
                            @elseif($attendance->check_in_method === 'one_tap')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800" title="One-tap check-in via Mobile App">
                                    Mobile (One-Tap)
                                </span>
                            @elseif($attendance->check_in_method === 'bulk_manual')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800" title="Bulk checked in on Web">
                                    Web (Bulk)
                                </span>
                            @elseif($attendance->check_in_method === 'manual')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800" title="Manually checked in on Web">
                                    Web (Manual)
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->check_in_method)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $attendance->is_present ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $attendance->is_present ? 'Present' : 'Absent' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            No attendance records for this service.
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

// Dynamic Service Filtering
async function loadFilteredServices() {
    const year = document.getElementById('filter_year').value;
    const month = document.getElementById('filter_month').value;
    const serviceSelect = document.getElementById('service_id');
    const currentSelected = serviceSelect.value;
    
    try {
        const response = await fetch(`/attendance/bulk-marking/services`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ month, year })
        });
        
        const data = await response.json();
        if(data.success) {
            serviceSelect.innerHTML = '<option value="">Choose a service...</option>';
            data.services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                if(service.id == currentSelected) option.selected = true;
                serviceSelect.appendChild(option);
            });
        }
    } catch(err) {
        console.error("Error loading services:", err);
    }
}

document.getElementById('filter_year').addEventListener('change', loadFilteredServices);
document.getElementById('filter_month').addEventListener('change', loadFilteredServices);

// Load initially if no services are pre-loaded
if (document.getElementById('service_id').options.length <= 1) {
    loadFilteredServices();
}

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
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
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