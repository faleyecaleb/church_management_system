@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Edit Status</h2>
                <p class="text-gray-600">{{ $member->name }}</p>
            </div>
            <a href="{{ route('membership.status.index', $member) }}" class="text-gray-600 hover:text-gray-800">
                Back to Status History
            </a>
        </div>

        @include('components.validation-errors')
        @include('components.success-message')

        <form action="{{ route('membership.status.update', [$member, $status]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" 
                        class="w-full rounded-md border-gray-300" 
                        required
                        onchange="handleStatusChange(this.value)">
                    <option value="">Select Status</option>
                    @foreach($availableStatuses as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $status->status) === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Effective Date</label>
                <input type="datetime-local" 
                       name="start_date" 
                       id="start_date" 
                       value="{{ old('start_date', $status->start_date->format('Y-m-d\TH:i')) }}" 
                       class="w-full rounded-md border-gray-300" 
                       required>
            </div>

            <!-- Membership Class -->
            <div>
                <div class="flex items-center">
                    <input type="checkbox" 
                           name="class_completed" 
                           id="class_completed" 
                           value="1" 
                           {{ old('class_completed', $status->class_completed) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="class_completed" class="ml-2 block text-sm text-gray-700">
                        Membership Class Completed
                    </label>
                </div>
            </div>

            <!-- Transfer Details -->
            <div id="transfer-details" class="space-y-4" style="display: none;">
                <div>
                    <label for="transfer_church" class="block text-sm font-medium text-gray-700 mb-1">Transfer to Church</label>
                    <input type="text" 
                           name="transfer_church" 
                           id="transfer_church" 
                           value="{{ old('transfer_church', $status->transfer_church) }}" 
                           class="w-full rounded-md border-gray-300"
                           placeholder="Enter church name">
                </div>

                <div>
                    <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1">Transfer Date</label>
                    <input type="date" 
                           name="transfer_date" 
                           id="transfer_date" 
                           value="{{ old('transfer_date', $status->transfer_date?->format('Y-m-d')) }}" 
                           class="w-full rounded-md border-gray-300">
                </div>
            </div>

            <!-- Renewal Date -->
            <div>
                <label for="renewal_date" class="block text-sm font-medium text-gray-700 mb-1">Renewal Date (Optional)</label>
                <input type="date" 
                       name="renewal_date" 
                       id="renewal_date" 
                       value="{{ old('renewal_date', $status->renewal_date?->format('Y-m-d')) }}" 
                       class="w-full rounded-md border-gray-300">
                <p class="mt-1 text-sm text-gray-500">Set a date for membership renewal if applicable</p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          class="w-full rounded-md border-gray-300" 
                          placeholder="Add any relevant notes about this status">{{ old('notes', $status->notes) }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('membership.status.index', $member) }}" 
                   class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">
                    Update Status
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function handleStatusChange(status) {
    const transferDetails = document.getElementById('transfer-details');
    const transferChurch = document.getElementById('transfer_church');
    const transferDate = document.getElementById('transfer_date');

    if (status === 'transferred') {
        transferDetails.style.display = 'block';
        transferChurch.required = true;
        transferDate.required = true;
    } else {
        transferDetails.style.display = 'none';
        transferChurch.required = false;
        transferDate.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    handleStatusChange(statusSelect.value);
});
</script>
@endpush
@endsection