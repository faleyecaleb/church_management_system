@extends('layouts.admin')

@section('title', 'Mark Attendance - Select Members')
@section('header', 'Attendance Marking')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Alert Messages -->
    <div id="alert-container" class="mb-4 hidden">
        <div id="success-alert" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span id="success-message" class="block sm:inline"></span>
        </div>
        <div id="error-alert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span id="error-message" class="block sm:inline"></span>
        </div>
    </div>
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Member Attendance</h2>
            <p class="text-sm text-gray-500">Search, filter and select members to mark attendance</p>
        </div>

        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('attendance.marking') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Step 1
            </a>
            <div class="text-sm text-gray-500">
                <span class="font-medium">Step 2 of 2</span>
            </div>
        </div>

        <div class="bg-primary-50 rounded-xl p-4 mb-6">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                <div>
                    <h3 class="text-lg font-medium text-primary-700">{{ $service->name }}</h3>
                    <p class="text-primary-600">{{ $attendanceDate->format('l, F j, Y') }}</p>
                </div>
                <div class="mt-2 md:mt-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        {{ $defaultStatus === 'present' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $defaultStatus === 'absent' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $defaultStatus === 'late' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    ">
                        Default: {{ ucfirst($defaultStatus) }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('attendance.marking.step2.process') }}" method="POST" id="attendance-form" onsubmit="return validateForm(event)">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="attendance_date" value="{{ $attendanceDate->format('Y-m-d') }}">

            <!-- Filter Controls -->
            <div class="mb-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="member-search" placeholder="Enter member name..." 
                                class="pl-10 w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="w-full md:w-48">
                        <select id="department-filter" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">All Departments</option>
                            @php
                                $departments = $members->pluck('department')->filter()->unique()->sort();
                            @endphp
                            @foreach($departments as $department)
                                <option value="{{ $department }}">{{ $department }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-48">
                        <select id="status-filter" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">All Statuses</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="mb-6 bg-blue-50 rounded-xl p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Quick Tips</h3>
                        <div class="mt-1 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Type a name to quickly search</li>
                                <li>Click on a status button to change it</li>
                                <li>All members are set to the default status initially</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Members List -->
            <div class="overflow-x-auto">
                <div class="inline-block min-w-full align-middle">
                    <div class="overflow-hidden border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Member
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mark Attendance
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="members-table-body">
                                @forelse($members as $member)
                                    @php
                                        $existingStatus = null;
                                        if (isset($existingAttendance[$member->id])) {
                                            $checkInTime = $existingAttendance[$member->id]->check_in_time;
                                            $serviceStartTime = Carbon\Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $service->start_time->format('H:i:s'));
                                            $existingStatus = $checkInTime->gt($serviceStartTime->addMinutes(15)) ? 'late' : 'present';
                                        }
                                        $status = $existingStatus ?? $defaultStatus;
                                    @endphp
                                    <tr class="hover:bg-gray-50 member-row" data-department="{{ $member->department }}" data-status="{{ $status }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $member->profile_photo_url }}" alt="{{ $member->full_name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member->first_name }} {{ $member->last_name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $member->phone }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $member->department ?: 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <button type="button" 
                                                    class="status-btn px-3 py-1 rounded-full text-xs font-medium {{ $status === 'present' ? 'bg-green-100 text-green-800 ring-2 ring-green-600' : 'bg-gray-100 text-gray-800 hover:bg-green-50 hover:text-green-700' }}"
                                                    onclick="updateStatus(this, '{{ $member->id }}', 'present')"
                                                    data-is-present="1"
                                                    data-is-absent="0">
                                                    Present
                                                </button>
                                                <button type="button" 
                                                    class="status-btn px-3 py-1 rounded-full text-xs font-medium {{ $status === 'absent' ? 'bg-red-100 text-red-800 ring-2 ring-red-600' : 'bg-gray-100 text-gray-800 hover:bg-red-50 hover:text-red-700' }}"
                                                    onclick="updateStatus(this, '{{ $member->id }}', 'absent')"
                                                    data-is-present="0"
                                                    data-is-absent="1">
                                                    Absent
                                                </button>
                                                <button type="button" 
                                                    class="status-btn px-3 py-1 rounded-full text-xs font-medium {{ $status === 'late' ? 'bg-yellow-100 text-yellow-800 ring-2 ring-yellow-600' : 'bg-gray-100 text-gray-800 hover:bg-yellow-50 hover:text-yellow-700' }}"
                                                    onclick="updateStatus(this, '{{ $member->id }}', 'late')">
                                                    Late
                                                </button>
                                                <input type="hidden" name="member_status[{{ $member->id }}]" value="{{ $status }}">
                                                <input type="hidden" name="is_present[{{ $member->id }}]" value="{{ $status === 'present' || $status === 'late' ? '1' : '0' }}">
                                                <input type="hidden" name="is_absent[{{ $member->id }}]" value="{{ $status === 'absent' ? '1' : '0' }}">
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No members found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" id="submit-button" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors min-w-[150px] justify-center">
                    <span>Save Attendance</span>
                    <svg id="loading-spinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const memberSearch = document.getElementById('member-search');
        const departmentFilter = document.getElementById('department-filter');
        const statusFilter = document.getElementById('status-filter');
        const memberRows = document.querySelectorAll('.member-row');

        function filterMembers() {
            const searchTerm = memberSearch.value.toLowerCase();
            const departmentValue = departmentFilter.value;
            const statusValue = statusFilter.value;

            memberRows.forEach(row => {
                const memberName = row.querySelector('.text-gray-900').textContent.toLowerCase();
                const memberDepartment = row.getAttribute('data-department') || '';
                const memberStatus = row.getAttribute('data-status');

                const matchesSearch = memberName.includes(searchTerm);
                const matchesDepartment = !departmentValue || memberDepartment === departmentValue;
                const matchesStatus = !statusValue || memberStatus === statusValue;

                if (matchesSearch && matchesDepartment && matchesStatus) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        memberSearch.addEventListener('input', filterMembers);
        departmentFilter.addEventListener('change', filterMembers);
        statusFilter.addEventListener('change', filterMembers);
    });

    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        const successMessage = document.getElementById('success-message');
        const errorMessage = document.getElementById('error-message');

        alertContainer.classList.remove('hidden');
        if (type === 'success') {
            successAlert.classList.remove('hidden');
            errorAlert.classList.add('hidden');
            successMessage.textContent = message;
        } else {
            errorAlert.classList.remove('hidden');
            successAlert.classList.add('hidden');
            errorMessage.textContent = message;
        }

        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertContainer.classList.add('hidden');
            successAlert.classList.add('hidden');
            errorAlert.classList.add('hidden');
        }, 5000);
    }

    function validateForm(event) {
        event.preventDefault();
        const form = event.target;
        const submitButton = form.querySelector('#submit-button');
        const submitText = submitButton.querySelector('span');
        const loadingSpinner = submitButton.querySelector('#loading-spinner');
        const formData = new FormData(form);

        // Disable submit button and show loading state
        submitButton.disabled = true;
        submitText.textContent = 'Saving...';
        loadingSpinner.classList.remove('hidden');

        // Log the form data being sent
        const formDataObj = {};
        formData.forEach((value, key) => {
            formDataObj[key] = value;
        });
        console.log('Submitting form data:', formDataObj);

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            showAlert('success', data.message || 'Attendance saved successfully');
            // Wait a moment to show the success message before redirecting
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1500);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', error.error || 'Failed to save attendance. Please try again.');
            // Reset submit button
            submitButton.disabled = false;
            submitText.textContent = 'Save Attendance';
            loadingSpinner.classList.add('hidden');
        });

        return false;
    }

    function updateStatus(button, memberId, status) {
        // Update hidden input value
        const hiddenInput = button.parentNode.querySelector(`input[name="member_status[${memberId}]"]`);
        hiddenInput.value = status;

        // Remove existing is_present and is_absent inputs
        const parent = button.parentNode;
        const existingInputs = parent.querySelectorAll(`input[name^="is_"]`);
        existingInputs.forEach(input => input.remove());

        // Update button styles
        const buttons = button.parentNode.querySelectorAll('.status-btn');
        buttons.forEach(btn => {
            btn.classList.remove('ring-2', 'ring-green-600', 'ring-red-600', 'ring-yellow-600', 
                               'bg-green-100', 'text-green-800', 
                               'bg-red-100', 'text-red-800', 
                               'bg-yellow-100', 'text-yellow-800');
            btn.classList.add('bg-gray-100', 'text-gray-800');
        });

        // Add active styles to clicked button
        button.classList.remove('bg-gray-100', 'text-gray-800');
        if (status === 'present') {
            button.classList.add('bg-green-100', 'text-green-800', 'ring-2', 'ring-green-600');
            // Update is_present and is_absent hidden inputs
            const hiddenIsPresent = document.createElement('input');
            hiddenIsPresent.type = 'hidden';
            hiddenIsPresent.name = `is_present[${memberId}]`;
            hiddenIsPresent.value = '1';
            const hiddenIsAbsent = document.createElement('input');
            hiddenIsAbsent.type = 'hidden';
            hiddenIsAbsent.name = `is_absent[${memberId}]`;
            hiddenIsAbsent.value = '0';
            button.parentNode.appendChild(hiddenIsPresent);
            button.parentNode.appendChild(hiddenIsAbsent);
        } else if (status === 'absent') {
            button.classList.add('bg-red-100', 'text-red-800', 'ring-2', 'ring-red-600');
            // Update is_present and is_absent hidden inputs
            const hiddenIsPresent = document.createElement('input');
            hiddenIsPresent.type = 'hidden';
            hiddenIsPresent.name = `is_present[${memberId}]`;
            hiddenIsPresent.value = '0';
            const hiddenIsAbsent = document.createElement('input');
            hiddenIsAbsent.type = 'hidden';
            hiddenIsAbsent.name = `is_absent[${memberId}]`;
            hiddenIsAbsent.value = '1';
            button.parentNode.appendChild(hiddenIsPresent);
            button.parentNode.appendChild(hiddenIsAbsent);
        } else if (status === 'late') {
            button.classList.add('bg-yellow-100', 'text-yellow-800', 'ring-2', 'ring-yellow-600');
            // Late members are considered present
            const hiddenIsPresent = document.createElement('input');
            hiddenIsPresent.type = 'hidden';
            hiddenIsPresent.name = `is_present[${memberId}]`;
            hiddenIsPresent.value = '1';
            const hiddenIsAbsent = document.createElement('input');
            hiddenIsAbsent.type = 'hidden';
            hiddenIsAbsent.name = `is_absent[${memberId}]`;
            hiddenIsAbsent.value = '0';
            button.parentNode.appendChild(hiddenIsPresent);
            button.parentNode.appendChild(hiddenIsAbsent);
        }

        // Update row data attribute for filtering
        const row = button.closest('.member-row');
        row.setAttribute('data-status', status);
    }
</script>
@endpush

@endsection