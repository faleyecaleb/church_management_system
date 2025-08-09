@extends('layouts.admin')

@section('title', 'New Complaint')
@section('header', 'Create Complaint')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('complaints.store') }}" method="POST" enctype="multipart/form-data" x-data="{
            selectedMember: '{{ old('member_id') }}',
            followUpRequired: {{ old('follow_up_required') ? 'true' : 'false' }},
            anonymous: {{ old('is_anonymous') ? 'true' : 'false' }}
        }">
            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="text-red-700 font-semibold mb-2">Please fix the following errors:</div>
                    <ul class="list-disc list-inside text-red-700 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Member (optional) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Member (optional)</label>
                    <select name="member_id" x-model="selectedMember" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Not a registered member --</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->first_name }} {{ $member->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Anonymous -->
                <div class="flex items-center mt-6 md:mt-0">
                    <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" x-model="anonymous" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="is_anonymous" class="ml-2 text-sm text-gray-700">Submit as anonymous</label>
                    @error('is_anonymous')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Complainant Name -->
                <div x-show="!selectedMember && !anonymous" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complainant Name</label>
                    <input type="text" name="complainant_name" :disabled="selectedMember || anonymous" value="{{ old('complainant_name') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Full name">
                    @error('complainant_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Complainant Email -->
                <div x-show="!selectedMember && !anonymous" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complainant Email</label>
                    <input type="email" name="complainant_email" :disabled="selectedMember || anonymous" value="{{ old('complainant_email') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="name@example.com">
                    @error('complainant_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Complainant Phone -->
                <div x-show="!selectedMember && !anonymous" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complainant Phone (optional)</label>
                    <input type="text" name="complainant_phone" :disabled="selectedMember || anonymous" value="{{ old('complainant_phone') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. +1 555 123 4567">
                    @error('complainant_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department (optional)</label>
                    <select name="department" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- None --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>{{ $department }}</option>
                        @endforeach
                    </select>
                    @error('department')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach(\App\Models\Complaint::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                    <select name="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        @foreach(\App\Models\Complaint::PRIORITIES as $key => $label)
                            <option value="{{ $key }}" {{ old('priority', 'medium') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assigned To -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign To (optional)</label>
                    <select name="assigned_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Unassigned --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subject -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Brief summary of the complaint">
                    @error('subject')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="6" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Provide the full details of the complaint...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Follow Up Required -->
                <div class="md:col-span-1 flex items-center">
                    <input type="checkbox" id="follow_up_required" name="follow_up_required" value="1" x-model="followUpRequired" class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="follow_up_required" class="ml-2 text-sm text-gray-700">Follow-up required</label>
                    @error('follow_up_required')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Follow Up Date -->
                <div x-show="followUpRequired" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Date</label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date') }}" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('follow_up_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Evidence Files -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Evidence Files (optional)</label>
                    <input type="file" name="evidence_files[]" multiple class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                    <p class="mt-1 text-xs text-gray-500">Accepted: jpg, jpeg, png, pdf, doc, docx, txt. Max 10MB per file.</p>
                    @error('evidence_files.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center space-x-3">
                <a href="{{ route('complaints.index') }}" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Create Complaint</button>
            </div>
        </form>
    </div>
</div>
@endsection
