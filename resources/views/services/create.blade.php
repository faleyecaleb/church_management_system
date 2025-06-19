@extends('layouts.admin')

@section('title', 'Add Service')
@section('header', 'Create New Service')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Add New Service</h2>
                <p class="text-sm text-gray-500 mt-1">Create a new service schedule</p>
            </div>
            <a href="{{ route('services.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Services
            </a>
        </div>

        @include('components.validation-errors')
        @include('components.success-message')

        <form action="{{ route('services.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Service Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Service Name</label>
                <input type="text" name="name" id="name" 
                    value="{{ old('name') }}" 
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                    required
                    placeholder="e.g., Sunday Morning Service">
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                <textarea name="description" id="description" rows="3" 
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                    placeholder="Brief description of the service">{{ old('description') }}</textarea>
            </div>

            <!-- Schedule -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-1">Day of Week</label>
                    <select name="day_of_week" id="day_of_week" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" required>
                        <option value="">Select Day</option>
                        <option value="sunday" {{ old('day_of_week') === 'sunday' ? 'selected' : '' }}>Sunday</option>
                        <option value="monday" {{ old('day_of_week') === 'monday' ? 'selected' : '' }}>Monday</option>
                        <option value="tuesday" {{ old('day_of_week') === 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                        <option value="wednesday" {{ old('day_of_week') === 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                        <option value="thursday" {{ old('day_of_week') === 'thursday' ? 'selected' : '' }}>Thursday</option>
                        <option value="friday" {{ old('day_of_week') === 'friday' ? 'selected' : '' }}>Friday</option>
                        <option value="saturday" {{ old('day_of_week') === 'saturday' ? 'selected' : '' }}>Saturday</option>
                    </select>
                </div>
                <div>
                    <label for="is_recurring" class="block text-sm font-medium text-gray-700 mb-1">Service Type</label>
                    <select name="is_recurring" id="is_recurring" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" required>
                        <option value="1" {{ old('is_recurring', '1') === '1' ? 'selected' : '' }}>Recurring Weekly</option>
                        <option value="0" {{ old('is_recurring') === '0' ? 'selected' : '' }}>One-time Service</option>
                    </select>
                </div>
            </div>

            <!-- Time -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                    <input type="time" name="start_time" id="start_time" 
                        value="{{ old('start_time') }}" 
                        class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                        required>
                </div>
                <div>
                    <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                    <input type="time" name="end_time" id="end_time" 
                        value="{{ old('end_time') }}" 
                        class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                        required>
                </div>
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" id="location" 
                    value="{{ old('location') }}" 
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                    required
                    placeholder="e.g., Main Sanctuary">
            </div>

            <!-- Capacity -->
            <div>
                <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacity (Optional)</label>
                <input type="number" name="capacity" id="capacity" 
                    value="{{ old('capacity') }}" 
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                    min="1"
                    placeholder="Leave empty for unlimited capacity">
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" required>
                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                <textarea name="notes" id="notes" rows="3" 
                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow" 
                    placeholder="Any additional information about the service">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Create Service
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function validateTimes() {
        if (startTimeInput.value && endTimeInput.value) {
            if (startTimeInput.value >= endTimeInput.value) {
                endTimeInput.setCustomValidity('End time must be after start time');
            } else {
                endTimeInput.setCustomValidity('');
            }
        }
    }

    startTimeInput.addEventListener('change', validateTimes);
    endTimeInput.addEventListener('change', validateTimes);
});
</script>
@endpush
@endsection