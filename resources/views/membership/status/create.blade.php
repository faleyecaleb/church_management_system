@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Change Membership Status</h2>
                <p class="text-gray-600">{{ $member->name }}</p>
            </div>
            <a href="{{ route('membership.status.index', $member) }}" class="text-gray-600 hover:text-gray-800">
                Back to Status History
            </a>
        </div>

        @include('components.validation-errors')
        @include('components.success-message')

        <!-- Current Status Info -->
        @if($currentStatus)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Current Status</h3>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ 
                    match($currentStatus->status) {
                        'active' => 'bg-green-100 text-green-800',
                        'inactive' => 'bg-red-100 text-red-800',
                        'new' => 'bg-blue-100 text-blue-800',
                        'transferred' => 'bg-yellow-100 text-yellow-800',
                        default => 'bg-gray-100 text-gray-800'
                    }
                }}">
                    {{ $availableStatuses[$currentStatus->status] }}
                </span>
                <span class="text-sm text-gray-600">
                    Since {{ $currentStatus->start_date->format('M d, Y') }}
                </span>
            </div>
        </div>
        @endif

        <form action="{{ route('membership.status.store', $member) }}" method="POST" class="space-y-6">
            @csrf

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                <select name="status" id="status" 
                        class="w-full rounded-md border-gray-300" 
                        required
                        onchange="handleStatusChange(this.value)">
                    <option value="">Select Status</option>
                    @foreach($availableStatuses as $value => $label)
                        <option value="{{ $value }}" {{ old('status') === $value ? 'selected' : '' }}>
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
                       value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}" 
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
                           {{ old('class_completed') ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="class_completed" class="ml-2 block text-sm text-gray-700">
                        Membership Class Completed
                    </label>
                </div>
            </div>

            <!-- Transfer Details (conditionally shown) -->
            <div id="transfer-details" class="space-y-4" style="display: none;">
                <div>
                    <label for="transfer_church" class="block text-sm font-medium text-gray-700 mb-1">Transfer to Church</label>
                    <input type="text" 
                           name="transfer_church" 
                           id="transfer_church" 
                           value="{{ old('transfer_church') }}" 
                           class="w-full rounded-md border-gray-300"
                           placeholder="Enter church name">
                </div>

                <div>
                    <label for="transfer_date" class="block text-sm font-medium text-gray-700 mb-1">Transfer Date</label>
                    <input type="date" 
                           name="transfer_date" 
                           id="transfer_date" 
                           value="{{ old('transfer_date') }}" 
                           class="w-full rounded-md border-gray-300">
                </div>
            </div>

            <!-- Renewal Date -->
            <div>
                <label for="renewal_date" class="block text-sm font-medium text-gray-700 mb-1">Renewal Date (Optional)</label>
                <input type="date" 
                       name="renewal_date" 
                       id="renewal_date" 
                       value="{{ old('renewal_date') }}" 
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
                          placeholder="Add any relevant notes about this status change">{{ old('notes') }}</textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
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