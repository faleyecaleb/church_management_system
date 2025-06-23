@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Record Attendance</h2>
            <a href="{{ route('attendance.service') }}" class="text-gray-600 hover:text-gray-800">
                Back to Attendance
            </a>
        </div>

        @include('components.validation-errors')
        @include('components.success-message')

        <form action="{{ route('attendance.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Member Selection -->
            <div>
                <label for="member_id" class="block text-sm font-medium text-gray-700 mb-1">Member</label>
                <select name="member_id" id="member_id" class="w-full rounded-md border-gray-300" required>
                    <option value="">Select Member</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->first_name }} {{ $member->last_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Service Selection -->
            <div>
                <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                <select name="service_id" id="service_id" class="w-full rounded-md border-gray-300" required>
                    <option value="">Select Service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }} ({{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Check-in Time -->
            <div>
                <label for="check_in_time" class="block text-sm font-medium text-gray-700 mb-1">Check-in Time</label>
                <input type="datetime-local" name="check_in_time" id="check_in_time" 
                    value="{{ old('check_in_time', now()->format('Y-m-d\TH:i')) }}" 
                    class="w-full rounded-md border-gray-300" 
                    required>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                <textarea name="notes" id="notes" rows="3" 
                    class="w-full rounded-md border-gray-300" 
                    placeholder="Any additional notes about this attendance">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Record Attendance
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize select2 for better member selection experience
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#member_id').select2({
            placeholder: 'Select Member',
            allowClear: true
        });
    }

    // Service availability check
    const serviceSelect = document.getElementById('service_id');
    const checkInTimeInput = document.getElementById('check_in_time');

    function updateServiceAvailability() {
        const selectedService = serviceSelect.options[serviceSelect.selectedIndex];
        if (selectedService.value) {
            // You might want to add an AJAX call here to check service availability
            // based on the selected time and service capacity
        }
    }

    serviceSelect.addEventListener('change', updateServiceAvailability);
    checkInTimeInput.addEventListener('change', updateServiceAvailability);
});
</script>
@endpush
@endsection