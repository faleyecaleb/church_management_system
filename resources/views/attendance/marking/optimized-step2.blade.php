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

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-white">Mark Attendance</h2>
                    <p class="text-indigo-100">{{ $service->name }} - {{ $attendanceDate }}</p>
                </div>
                <div class="text-right">
                    <div class="text-white text-sm">
                        <span id="selected-count">0</span> of <span id="total-count">0</span> selected
                    </div>
                    <div class="text-indigo-200 text-xs">Default: {{ ucfirst($defaultStatus) }}</div>
                </div>
            </div>
        </div>

        <!-- Controls Panel -->
        <div class="px-6 py-4 bg-gray-50 border-b">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                <!-- Search -->
                <div class="lg:col-span-4">
                    <label for="search" class="block text-xs font-medium text-gray-700 mb-1">Search Members</label>
                    <div class="relative">
                        <input type="text" id="search" placeholder="Search by name, email, or phone..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Department Filter -->
                <div class="lg:col-span-2">
                    <label for="department-filter" class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                    <select id="department-filter" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Departments</option>
                    </select>
                </div>

                <!-- Gender Filter -->
                <div class="lg:col-span-2">
                    <label for="gender-filter" class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                    <select id="gender-filter" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Genders</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="lg:col-span-2">
                    <label for="status-filter" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select id="status-filter" class="w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Clear Filters -->
                <div class="lg:col-span-2">
                    <button id="clear-filters" class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="mt-4 flex flex-wrap gap-2">
                <button id="select-all" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                    Select All Visible
                </button>
                <button id="deselect-all" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500">
                    Deselect All
                </button>
                <button id="select-by-department" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-purple-500">
                    Select by Department
                </button>
                <button id="invert-selection" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500">
                    Invert Selection
                </button>
            </div>
        </div>

        <!-- Members List with Virtual Scrolling -->
        <div class="relative">
            <div id="members-container" class="h-96 overflow-auto border-b">
                <div id="virtual-list" class="relative">
                    <!-- Virtual scrolling content will be inserted here -->
                </div>
            </div>
            
            <!-- Loading indicator -->
            <div id="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center hidden">
                <div class="flex items-center space-x-2">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
                    <span class="text-gray-600">Loading members...</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
            <a href="{{ route('attendance.marking') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Step 1
            </a>
            
            <div class="flex space-x-3">
                <button id="mark-attendance-btn" class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Mark Attendance (<span id="btn-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Department Selection Modal -->
<div id="department-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Select by Department</h3>
            <div id="department-checkboxes" class="space-y-2 max-h-60 overflow-y-auto">
                <!-- Department checkboxes will be populated here -->
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button id="cancel-dept-selection" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </button>
                <button id="apply-dept-selection" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                    Apply Selection
                </button>
            </div>
        </div>
    </div>
</div>

<script>
class AttendanceMarking {
    constructor() {
        this.members = [];
        this.filteredMembers = [];
        this.selectedMembers = new Set();
        this.virtualList = document.getElementById('virtual-list');
        this.container = document.getElementById('members-container');
        this.itemHeight = 60; // Height of each member row
        this.visibleItems = Math.ceil(this.container.clientHeight / this.itemHeight) + 5;
        this.startIndex = 0;
        this.endIndex = this.visibleItems;
        
        this.serviceId = {{ $service->id }};
        this.attendanceDate = '{{ $attendanceDate }}';
        this.defaultStatus = '{{ $defaultStatus }}';
        
        this.init();
    }

    async init() {
        await this.loadMembers();
        this.setupEventListeners();
        this.renderVirtualList();
        this.updateCounts();
    }

    async loadMembers() {
        try {
            document.getElementById('loading').classList.remove('hidden');
            
            const response = await fetch(`/api/members/for-attendance?service_id=${this.serviceId}&date=${this.attendanceDate}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) throw new Error('Failed to load members');
            
            const data = await response.json();
            this.members = data.members;
            this.filteredMembers = [...this.members];
            
            // Populate department filter
            this.populateDepartmentFilter();
            
        } catch (error) {
            this.showError('Failed to load members: ' + error.message);
        } finally {
            document.getElementById('loading').classList.add('hidden');
        }
    }

    populateDepartmentFilter() {
        const departments = [...new Set(this.members.flatMap(m => m.departments || []))].sort();
        const select = document.getElementById('department-filter');
        
        departments.forEach(dept => {
            const option = document.createElement('option');
            option.value = dept;
            option.textContent = dept;
            select.appendChild(option);
        });
    }

    setupEventListeners() {
        // Search
        const searchInput = document.getElementById('search');
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => this.filterMembers(), 300);
        });

        // Filters
        ['department-filter', 'gender-filter', 'status-filter'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => this.filterMembers());
        });

        // Clear filters
        document.getElementById('clear-filters').addEventListener('click', () => this.clearFilters());

        // Bulk actions
        document.getElementById('select-all').addEventListener('click', () => this.selectAllVisible());
        document.getElementById('deselect-all').addEventListener('click', () => this.deselectAll());
        document.getElementById('select-by-department').addEventListener('click', () => this.showDepartmentModal());
        document.getElementById('invert-selection').addEventListener('click', () => this.invertSelection());

        // Virtual scrolling
        this.container.addEventListener('scroll', () => this.handleScroll());

        // Mark attendance
        document.getElementById('mark-attendance-btn').addEventListener('click', () => this.markAttendance());

        // Department modal
        document.getElementById('cancel-dept-selection').addEventListener('click', () => this.hideDepartmentModal());
        document.getElementById('apply-dept-selection').addEventListener('click', () => this.applyDepartmentSelection());
    }

    filterMembers() {
        const search = document.getElementById('search').value.toLowerCase();
        const department = document.getElementById('department-filter').value;
        const gender = document.getElementById('gender-filter').value;
        const status = document.getElementById('status-filter').value;

        this.filteredMembers = this.members.filter(member => {
            const matchesSearch = !search || 
                member.first_name.toLowerCase().includes(search) ||
                member.last_name.toLowerCase().includes(search) ||
                member.email.toLowerCase().includes(search) ||
                (member.phone && member.phone.includes(search));

            const matchesDepartment = !department || 
                (member.departments && member.departments.includes(department));

            const matchesGender = !gender || member.gender === gender;
            const matchesStatus = !status || member.membership_status === status;

            return matchesSearch && matchesDepartment && matchesGender && matchesStatus;
        });

        this.startIndex = 0;
        this.endIndex = this.visibleItems;
        this.renderVirtualList();
        this.updateCounts();
    }

    clearFilters() {
        document.getElementById('search').value = '';
        document.getElementById('department-filter').value = '';
        document.getElementById('gender-filter').value = '';
        document.getElementById('status-filter').value = '';
        this.filterMembers();
    }

    handleScroll() {
        const scrollTop = this.container.scrollTop;
        const newStartIndex = Math.floor(scrollTop / this.itemHeight);
        
        if (newStartIndex !== this.startIndex) {
            this.startIndex = newStartIndex;
            this.endIndex = Math.min(this.startIndex + this.visibleItems, this.filteredMembers.length);
            this.renderVirtualList();
        }
    }

    renderVirtualList() {
        const totalHeight = this.filteredMembers.length * this.itemHeight;
        const offsetY = this.startIndex * this.itemHeight;
        
        this.virtualList.style.height = totalHeight + 'px';
        
        const visibleMembers = this.filteredMembers.slice(this.startIndex, this.endIndex);
        
        this.virtualList.innerHTML = `
            <div style="transform: translateY(${offsetY}px);">
                ${visibleMembers.map((member, index) => this.renderMemberRow(member, this.startIndex + index)).join('')}
            </div>
        `;

        // Reattach event listeners for checkboxes
        this.virtualList.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const memberId = parseInt(e.target.value);
                if (e.target.checked) {
                    this.selectedMembers.add(memberId);
                } else {
                    this.selectedMembers.delete(memberId);
                }
                this.updateCounts();
            });
        });
    }

    renderMemberRow(member, index) {
        const isSelected = this.selectedMembers.has(member.id);
        const departments = member.departments ? member.departments.join(', ') : 'No Department';
        
        return `
            <div class="flex items-center px-4 py-3 border-b border-gray-200 hover:bg-gray-50 ${isSelected ? 'bg-indigo-50' : ''}" style="height: ${this.itemHeight}px;">
                <div class="flex items-center">
                    <input type="checkbox" value="${member.id}" ${isSelected ? 'checked' : ''} 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                </div>
                <div class="ml-4 flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate">
                                ${member.first_name} ${member.last_name}
                            </p>
                            <p class="text-sm text-gray-500 truncate">${member.email}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0 text-right">
                            <div class="text-xs text-gray-500">${departments}</div>
                            <div class="text-xs text-gray-400">${member.phone || 'No phone'}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    selectAllVisible() {
        this.filteredMembers.forEach(member => {
            this.selectedMembers.add(member.id);
        });
        this.renderVirtualList();
        this.updateCounts();
    }

    deselectAll() {
        this.selectedMembers.clear();
        this.renderVirtualList();
        this.updateCounts();
    }

    invertSelection() {
        const newSelection = new Set();
        this.filteredMembers.forEach(member => {
            if (!this.selectedMembers.has(member.id)) {
                newSelection.add(member.id);
            }
        });
        this.selectedMembers = newSelection;
        this.renderVirtualList();
        this.updateCounts();
    }

    showDepartmentModal() {
        const departments = [...new Set(this.members.flatMap(m => m.departments || []))].sort();
        const container = document.getElementById('department-checkboxes');
        
        container.innerHTML = departments.map(dept => `
            <label class="flex items-center">
                <input type="checkbox" value="${dept}" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">${dept}</span>
            </label>
        `).join('');
        
        document.getElementById('department-modal').classList.remove('hidden');
    }

    hideDepartmentModal() {
        document.getElementById('department-modal').classList.add('hidden');
    }

    applyDepartmentSelection() {
        const selectedDepts = Array.from(document.querySelectorAll('#department-checkboxes input:checked'))
            .map(cb => cb.value);
        
        if (selectedDepts.length === 0) {
            this.showError('Please select at least one department');
            return;
        }

        this.members.forEach(member => {
            if (member.departments && member.departments.some(dept => selectedDepts.includes(dept))) {
                this.selectedMembers.add(member.id);
            }
        });

        this.hideDepartmentModal();
        this.renderVirtualList();
        this.updateCounts();
    }

    updateCounts() {
        const selectedCount = this.selectedMembers.size;
        const totalCount = this.filteredMembers.length;
        
        document.getElementById('selected-count').textContent = selectedCount;
        document.getElementById('total-count').textContent = totalCount;
        document.getElementById('btn-count').textContent = selectedCount;
        
        const markBtn = document.getElementById('mark-attendance-btn');
        markBtn.disabled = selectedCount === 0;
    }

    async markAttendance() {
        if (this.selectedMembers.size === 0) {
            this.showError('Please select at least one member');
            return;
        }

        if (!confirm(`Mark attendance for ${this.selectedMembers.size} members?`)) {
            return;
        }

        try {
            const markBtn = document.getElementById('mark-attendance-btn');
            markBtn.disabled = true;
            markBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>Processing...';

            const response = await fetch('/attendance/bulk-mark', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    service_id: this.serviceId,
                    attendance_date: this.attendanceDate,
                    default_status: this.defaultStatus,
                    member_ids: Array.from(this.selectedMembers)
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to mark attendance');
            }

            this.showSuccess(`Successfully marked attendance for ${data.marked_count} members`);
            
            // Redirect to attendance dashboard after a short delay
            setTimeout(() => {
                window.location.href = '/attendance';
            }, 2000);

        } catch (error) {
            this.showError('Failed to mark attendance: ' + error.message);
            const markBtn = document.getElementById('mark-attendance-btn');
            markBtn.disabled = false;
            markBtn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>Mark Attendance (<span id="btn-count">' + this.selectedMembers.size + '</span>)';
        }
    }

    showSuccess(message) {
        document.getElementById('success-message').textContent = message;
        document.getElementById('success-alert').classList.remove('hidden');
        document.getElementById('alert-container').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('success-alert').classList.add('hidden');
            document.getElementById('alert-container').classList.add('hidden');
        }, 5000);
    }

    showError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('error-alert').classList.remove('hidden');
        document.getElementById('alert-container').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('error-alert').classList.add('hidden');
            document.getElementById('alert-container').classList.add('hidden');
        }, 5000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new AttendanceMarking();
});
</script>
@endsection