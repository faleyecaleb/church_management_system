@extends('layouts.admin')

@section('title', 'Create Notification')
@section('header', 'Create Notification')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Create New Notification</h2>
                <a href="{{ route('notifications.index') }}" class="text-gray-600 hover:text-gray-900">
                    Back to Notifications
                </a>
            </div>

            <form action="{{ route('notifications.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Notification Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Notification Type</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                        <option value="">Select Type</option>
                        <option value="birthday" {{ old('type') == 'birthday' ? 'selected' : '' }}>Birthday</option>
                        <option value="anniversary" {{ old('type') == 'anniversary' ? 'selected' : '' }}>Anniversary</option>
                        <option value="milestone" {{ old('type') == 'milestone' ? 'selected' : '' }}>Milestone</option>
                        <option value="followup" {{ old('type') == 'followup' ? 'selected' : '' }}>Follow-up</option>
                        <option value="sermon" {{ old('type') == 'sermon' ? 'selected' : '' }}>Sermon Banner</option>
                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Optional Image Upload (For Sermon Banners) -->
                <div id="image_upload_container" class="hidden">
                    <label for="image" class="block text-sm font-medium text-gray-700">Banner Image (Optional)</label>
                    <input type="file" name="image" id="image" accept="image/*"
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="mt-1 text-xs text-gray-500">Max size 2MB. Recommended for Sermon notifications.</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Message -->
                <div>                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" id="message" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                              required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Recipient Selection -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Recipient Target</label>
                    <div class="flex items-center space-x-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="selection_type" value="broadcast" checked
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Broadcast to All Members (Global Notification)</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="selection_type" value="target"
                                   class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700 font-medium">Target Specific Members</span>
                        </label>
                    </div>
                </div>

                <!-- Recipient Selection Section (Hidden by default) -->
                <div id="target_recipients_container" class="hidden space-y-4 border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <h4 class="text-sm font-semibold text-gray-700">Filter & Select Members</h4>
                    
                    <!-- Filters Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <!-- Search Name -->
                        <div>
                            <input type="text" id="member_search" placeholder="Search by name..." 
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                        </div>
                        <!-- Filter Gender -->
                        <div>
                            <select id="member_gender_filter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <!-- Filter Branch -->
                        <div>
                            <select id="member_branch_filter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm">
                                <option value="">All Branches</option>
                                <option value="1">Adult Church</option>
                                <option value="2">Youth Church</option>
                                <option value="3">Children Church</option>
                            </select>
                        </div>
                    </div>

                    <!-- Member Checklist -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between border-b pb-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" id="select_all_members" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                <span class="ml-2 text-xs font-semibold text-gray-600 uppercase">Select All Visible</span>
                            </label>
                            <span id="selection_count" class="text-xs font-bold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">0 selected</span>
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1 border border-gray-200 rounded-md p-2 bg-white">
                            @foreach($members as $member)
                                <div class="member-row flex items-center justify-between p-2 hover:bg-gray-50 rounded transition-colors">
                                    <label class="flex items-center space-x-3 cursor-pointer flex-1">
                                        <input type="checkbox" name="recipient_ids[]" value="{{ $member->id }}"
                                               class="member-checkbox h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                               data-name="{{ strtolower($member->full_name) }}"
                                               data-gender="{{ strtolower($member->gender ?? '') }}"
                                               data-church="{{ $member->church_id }}">
                                        <div>
                                            <span class="text-sm font-medium text-gray-800">{{ $member->full_name }}</span>
                                            <span class="text-xs text-gray-500 ml-1">({{ ucfirst($member->gender ?? 'Unspecified') }})</span>
                                        </div>
                                    </label>
                                    <div class="text-xs text-gray-400 font-semibold uppercase">
                                        @if($member->church_id == 1)
                                            Adult
                                        @elseif($member->church_id == 2)
                                            Youth
                                        @elseif($member->church_id == 3)
                                            Children
                                        @else
                                            Global
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('recipient_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Schedule -->
                <div x-data="{ scheduled: false }">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_scheduled" id="is_scheduled" 
                               x-model="scheduled"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_scheduled" class="ml-2 block text-sm text-gray-700">Schedule for later</label>
                    </div>

                    <div x-show="scheduled" class="mt-3">
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                               value="{{ old('scheduled_at') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('scheduled_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Data Fields -->
                <div id="additionalFields" class="space-y-4">
                    <!-- Dynamic fields will be added here based on notification type -->
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Create Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const additionalFields = document.getElementById('additionalFields');

        typeSelect.addEventListener('change', function() {
            additionalFields.innerHTML = ''; // Clear existing fields
            
            // Toggle Image Upload for Sermon and Custom
            const imageContainer = document.getElementById('image_upload_container');
            if (this.value === 'sermon' || this.value === 'custom') {
                imageContainer.classList.remove('hidden');
            } else {
                imageContainer.classList.add('hidden');
            }

            switch(this.value) {
                case 'followup':
                    additionalFields.innerHTML = `
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Follow-up Reason</label>
                            <input type="text" name="data[reason]" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                   placeholder="Reason for follow-up">
                        </div>
                    `;
                    break;

                case 'milestone':
                    additionalFields.innerHTML = `
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Milestone Years</label>
                            <input type="number" name="data[milestone_years]" min="1"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                   placeholder="Number of years">
                        </div>
                    `;
                    break;

                case 'custom':
                    additionalFields.innerHTML = `
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea name="data[notes]" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                      placeholder="Any additional information"></textarea>
                        </div>
                    `;
                    break;
            }
        });

        // Trigger change event if there's a pre-selected value
        if (typeSelect.value) {
            typeSelect.dispatchEvent(new Event('change'));
        }

        // --- Recipient Selector JS ---
        const selectionTypeRadios = document.querySelectorAll('input[name="selection_type"]');
        const targetRecipientsContainer = document.getElementById('target_recipients_container');
        const memberSearch = document.getElementById('member_search');
        const memberGenderFilter = document.getElementById('member_gender_filter');
        const memberBranchFilter = document.getElementById('member_branch_filter');
        const memberRows = document.querySelectorAll('.member-row');
        const selectAllCheckbox = document.getElementById('select_all_members');
        const selectionCountSpan = document.getElementById('selection_count');
        const memberCheckboxes = document.querySelectorAll('.member-checkbox');
        const formElement = document.querySelector('form');

        selectionTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'target') {
                    targetRecipientsContainer.classList.remove('hidden');
                } else {
                    targetRecipientsContainer.classList.add('hidden');
                }
            });
        });

        // Client-side filtering logic
        function filterMembers() {
            const searchText = memberSearch.value.toLowerCase().trim();
            const selectedGender = memberGenderFilter.value.toLowerCase();
            const selectedBranch = memberBranchFilter.value;

            memberRows.forEach(row => {
                const checkbox = row.querySelector('.member-checkbox');
                const name = checkbox.getAttribute('data-name');
                const gender = checkbox.getAttribute('data-gender');
                const churchId = checkbox.getAttribute('data-church');

                const matchesSearch = !searchText || name.includes(searchText);
                const matchesGender = !selectedGender || gender === selectedGender;
                const matchesBranch = !selectedBranch || churchId === selectedBranch;

                if (matchesSearch && matchesGender && matchesBranch) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
            updateSelectAllState();
        }

        memberSearch.addEventListener('input', filterMembers);
        memberGenderFilter.addEventListener('change', filterMembers);
        memberBranchFilter.addEventListener('change', filterMembers);

        // Select All visible members
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            memberRows.forEach(row => {
                if (!row.classList.contains('hidden')) {
                    const checkbox = row.querySelector('.member-checkbox');
                    checkbox.checked = isChecked;
                }
            });
            updateSelectionCount();
        });

        // Individual checkbox changes
        memberCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateSelectionCount();
                updateSelectAllState();
            });
        });

        function updateSelectionCount() {
            const selectedCount = document.querySelectorAll('.member-checkbox:checked').length;
            selectionCountSpan.textContent = `${selectedCount} selected`;
        }

        function updateSelectAllState() {
            const visibleCheckboxes = Array.from(memberRows)
                .filter(row => !row.classList.contains('hidden'))
                .map(row => row.querySelector('.member-checkbox'));

            if (visibleCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                return;
            }

            const allChecked = visibleCheckboxes.every(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
        }

        // Form Submit Validation
        formElement.addEventListener('submit', function(e) {
            const selectedType = document.querySelector('input[name="selection_type"]:checked').value;
            if (selectedType === 'target') {
                const checkedCount = document.querySelectorAll('.member-checkbox:checked').length;
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one target recipient, or choose Broadcast to All.');
                }
            }
        });
    });
</script>
@endpush

@endsection