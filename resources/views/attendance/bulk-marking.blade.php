@extends('layouts.admin')

@section('title', 'Bulk Attendance Marking')
@section('header', 'Bulk Attendance Marking')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="max-w-7xl mx-auto py-6">
    <!-- Alert Container -->
    <div id="alert-container" class="mb-4"></div>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <h2 class="text-2xl font-bold text-white">Bulk Attendance Marking</h2>
            <p class="mt-1 text-indigo-100">Efficiently mark attendance for large groups of members</p>
        </div>

        <div class="p-6">
            <!-- Service Selection -->
            <div class="mb-6 bg-gray-50 rounded-xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="service_id" class="block text-sm font-medium text-gray-700 mb-2">Service *</label>
                        <select name="service_id" id="service_id" required class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select service...</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }} - {{ $service->day_of_week }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="attendance_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="attendance_date" id="attendance_date" value="{{ now()->format('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="loadMembers()" id="load-members-btn" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
                            Load Members
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading" class="hidden text-center py-8">
                <div class="inline-flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-lg text-gray-600">Loading members...</span>
                </div>
            </div>

            <!-- Members Section -->
            <div id="members-section" class="hidden">
                <!-- Bulk Actions -->
                <div class="mb-6 bg-blue-50 rounded-xl p-4">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium text-gray-700">Bulk Actions:</span>
                            <button type="button" onclick="selectAll()" class="px-3 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                Select All
                            </button>
                            <button type="button" onclick="selectNone()" class="px-3 py-1 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm">
                                Select None
                            </button>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span id="selected-count" class="text-sm font-medium text-gray-700">0 selected</span>
                            <button type="button" onclick="markSelectedPresent()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50" disabled>
                                Mark Present
                            </button>
                            <button type="button" onclick="markSelectedAbsent()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50" disabled>
                                Mark Absent
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 bg-gray-50 rounded-xl p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="search" placeholder="Search by name..." class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="department-filter" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <select id="department-filter" class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Departments</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="clearFilters()" class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Members List -->
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="col-span-1">
                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="col-span-4">Name</div>
                            <div class="col-span-2">Department</div>
                            <div class="col-span-2">Status</div>
                            <div class="col-span-3">Actions</div>
                        </div>
                    </div>
                    <div id="members-list" class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                        <!-- Members will be loaded here -->
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing <span id="showing-from">0</span> to <span id="showing-to">0</span> of <span id="total-members">0</span> members
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="previousPage()" id="prev-btn" class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 disabled:opacity-50" disabled>
                            Previous
                        </button>
                        <span id="page-info" class="px-3 py-1 text-sm text-gray-700">Page 1 of 1</span>
                        <button type="button" onclick="nextPage()" id="next-btn" class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 disabled:opacity-50" disabled>
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let members = [];
let filteredMembers = [];
let currentPage = 1;
const itemsPerPage = 50;
let selectedMembers = new Set();

// Show alert messages
function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    
    alertContainer.innerHTML = `
        <div class="${alertClass} px-4 py-3 rounded border" role="alert">
            <span class="block sm:inline">${message}</span>
        </div>
    `;
    
    setTimeout(() => {
        alertContainer.innerHTML = '';
    }, 5000);
}

// Load members from server
async function loadMembers() {
    const serviceId = document.getElementById('service_id').value;
    const attendanceDate = document.getElementById('attendance_date').value;
    
    if (!serviceId || !attendanceDate) {
        showAlert('Please select service and date', 'error');
        return;
    }

    const loadBtn = document.getElementById('load-members-btn');
    loadBtn.disabled = true;
    loadBtn.textContent = 'Loading...';
    
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('members-section').classList.add('hidden');

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch('/attendance/bulk-marking/members', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                service_id: serviceId,
                attendance_date: attendanceDate
            })
        });

        const data = await response.json();
        
        if (response.ok && data.success) {
            members = data.members || [];
            filteredMembers = [...members];
            populateDepartmentFilter();
            currentPage = 1;
            selectedMembers.clear();
            renderMembers();
            updateSelectedCount();
            document.getElementById('members-section').classList.remove('hidden');
            showAlert(`Successfully loaded ${members.length} members`, 'success');
        } else {
            showAlert(data.error || 'Failed to load members', 'error');
        }
    } catch (error) {
        console.error('Error loading members:', error);
        showAlert('Network error. Please check your connection and try again.', 'error');
    } finally {
        document.getElementById('loading').classList.add('hidden');
        loadBtn.disabled = false;
        loadBtn.textContent = 'Load Members';
    }
}

// Populate department filter
function populateDepartmentFilter() {
    const departments = [...new Set(members.flatMap(m => m.departments || []))].sort();
    const departmentFilter = document.getElementById('department-filter');
    
    departmentFilter.innerHTML = '<option value="">All Departments</option>';
    departments.forEach(dept => {
        departmentFilter.innerHTML += `<option value="${dept}">${dept}</option>`;
    });
}

// Render members list
function renderMembers() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, filteredMembers.length);
    const pageMembers = filteredMembers.slice(startIndex, endIndex);
    
    const membersList = document.getElementById('members-list');
    membersList.innerHTML = '';
    
    pageMembers.forEach(member => {
        const isSelected = selectedMembers.has(member.id);
        const departments = member.departments ? member.departments.join(', ') : 'N/A';
        
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-4 px-4 py-3 hover:bg-gray-50 items-center';
        row.innerHTML = `
            <div class="col-span-1">
                <input type="checkbox" ${isSelected ? 'checked' : ''} 
                       onchange="toggleMemberSelection(${member.id})" 
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            </div>
            <div class="col-span-4">
                <div class="text-sm font-medium text-gray-900">${member.full_name}</div>
                <div class="text-sm text-gray-500">${member.email}</div>
            </div>
            <div class="col-span-2">
                <div class="text-sm text-gray-900">${departments}</div>
            </div>
            <div class="col-span-2">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${getStatusClass(member.status)}">
                    ${member.status || 'Unmarked'}
                </span>
            </div>
            <div class="col-span-3 flex space-x-1">
                <button onclick="markMemberStatus(${member.id}, 'present')" 
                        class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs hover:bg-green-200">
                    Present
                </button>
                <button onclick="markMemberStatus(${member.id}, 'absent')" 
                        class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs hover:bg-red-200">
                    Absent
                </button>
            </div>
        `;
        membersList.appendChild(row);
    });
    
    updatePagination();
}

// Get status class for styling
function getStatusClass(status) {
    switch(status) {
        case 'present': return 'bg-green-100 text-green-800';
        case 'absent': return 'bg-red-100 text-red-800';
        case 'late': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

// Update pagination
function updatePagination() {
    const totalPages = Math.ceil(filteredMembers.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage + 1;
    const endIndex = Math.min(currentPage * itemsPerPage, filteredMembers.length);
    
    document.getElementById('showing-from').textContent = filteredMembers.length > 0 ? startIndex : 0;
    document.getElementById('showing-to').textContent = endIndex;
    document.getElementById('total-members').textContent = filteredMembers.length;
    document.getElementById('page-info').textContent = `Page ${currentPage} of ${totalPages}`;
    
    document.getElementById('prev-btn').disabled = currentPage <= 1;
    document.getElementById('next-btn').disabled = currentPage >= totalPages;
}

// Selection functions
function toggleMemberSelection(memberId) {
    if (selectedMembers.has(memberId)) {
        selectedMembers.delete(memberId);
    } else {
        selectedMembers.add(memberId);
    }
    updateSelectedCount();
}

function selectAll() {
    filteredMembers.forEach(member => selectedMembers.add(member.id));
    updateSelectedCount();
    renderMembers();
}

function selectNone() {
    selectedMembers.clear();
    updateSelectedCount();
    renderMembers();
}

function toggleSelectAll() {
    const checkbox = document.getElementById('select-all-checkbox');
    if (checkbox.checked) {
        selectAll();
    } else {
        selectNone();
    }
}

function updateSelectedCount() {
    const count = selectedMembers.size;
    document.getElementById('selected-count').textContent = `${count} selected`;
    
    const markPresentBtn = document.querySelector('button[onclick="markSelectedPresent()"]');
    const markAbsentBtn = document.querySelector('button[onclick="markSelectedAbsent()"]');
    
    markPresentBtn.disabled = count === 0;
    markAbsentBtn.disabled = count === 0;
}

// Mark attendance functions
async function markSelectedPresent() {
    await markSelectedStatus('present');
}

async function markSelectedAbsent() {
    await markSelectedStatus('absent');
}

async function markSelectedStatus(status) {
    if (selectedMembers.size === 0) return;
    
    const serviceId = document.getElementById('service_id').value;
    const attendanceDate = document.getElementById('attendance_date').value;
    
    try {
        const response = await fetch('/attendance/bulk-marking/mark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                service_id: serviceId,
                attendance_date: attendanceDate,
                member_ids: Array.from(selectedMembers),
                status: status
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            members.forEach(member => {
                if (selectedMembers.has(member.id)) {
                    member.status = status;
                }
            });
            
            selectedMembers.clear();
            updateSelectedCount();
            renderMembers();
            
            showAlert(`Successfully marked ${data.count} members as ${status}`, 'success');
        } else {
            showAlert(data.error || 'Failed to mark attendance', 'error');
        }
    } catch (error) {
        showAlert('Error marking attendance: ' + error.message, 'error');
    }
}

async function markMemberStatus(memberId, status) {
    const serviceId = document.getElementById('service_id').value;
    const attendanceDate = document.getElementById('attendance_date').value;
    
    try {
        const response = await fetch('/attendance/bulk-marking/mark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                service_id: serviceId,
                attendance_date: attendanceDate,
                member_ids: [memberId],
                status: status
            })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            const member = members.find(m => m.id === memberId);
            if (member) {
                member.status = status;
            }
            renderMembers();
            showAlert(`Marked member as ${status}`, 'success');
        } else {
            showAlert(data.error || 'Failed to mark attendance', 'error');
        }
    } catch (error) {
        showAlert('Error marking attendance: ' + error.message, 'error');
    }
}

// Filter functions
function filterMembers() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const departmentFilter = document.getElementById('department-filter').value;
    
    filteredMembers = members.filter(member => {
        const matchesSearch = member.full_name.toLowerCase().includes(searchTerm) || 
                             member.email.toLowerCase().includes(searchTerm);
        
        const matchesDepartment = !departmentFilter || 
                                 (member.departments && member.departments.includes(departmentFilter));
        
        return matchesSearch && matchesDepartment;
    });
    
    currentPage = 1;
    renderMembers();
}

function clearFilters() {
    document.getElementById('search').value = '';
    document.getElementById('department-filter').value = '';
    filteredMembers = [...members];
    currentPage = 1;
    renderMembers();
}

// Pagination functions
function previousPage() {
    if (currentPage > 1) {
        currentPage--;
        renderMembers();
    }
}

function nextPage() {
    const totalPages = Math.ceil(filteredMembers.length / itemsPerPage);
    if (currentPage < totalPages) {
        currentPage++;
        renderMembers();
    }
}

// Event listeners
document.getElementById('search').addEventListener('input', filterMembers);
document.getElementById('department-filter').addEventListener('change', filterMembers);
</script>
@endsection