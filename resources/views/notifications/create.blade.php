@extends('layouts.app')

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

            <form action="{{ route('notifications.store') }}" method="POST" class="space-y-6">
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

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" id="message" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                              required>{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Recipient -->
                <div>
                    <label for="recipient_id" class="block text-sm font-medium text-gray-700">Recipient</label>
                    <select name="recipient_id" id="recipient_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                            required>
                        <option value="">Select Recipient</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" {{ old('recipient_id') == $member->id ? 'selected' : '' }}>
                                {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('recipient_id')
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
    });
</script>
@endpush

@endsection