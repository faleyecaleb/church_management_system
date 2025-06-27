<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="member_id" class="block text-sm font-medium text-gray-700">Member</label>
        <select name="member_id" id="member_id" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @foreach ($members as $member)
                <option value="{{ $member->id }}" {{ old('member_id', $pledge->member_id ?? '') == $member->id ? 'selected' : '' }}>
                    {{ $member->full_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="campaign_name" class="block text-sm font-medium text-gray-700">Campaign Name</label>
        <input type="text" name="campaign_name" id="campaign_name" value="{{ old('campaign_name', $pledge->campaign_name ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="total_amount" class="block text-sm font-medium text-gray-700">Total Amount</label>
        <input type="number" name="total_amount" id="total_amount" value="{{ old('total_amount', $pledge->total_amount ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="amount_paid" class="block text-sm font-medium text-gray-700">Amount Paid</label>
        <input type="number" name="amount_paid" id="amount_paid" value="{{ old('amount_paid', $pledge->amount_paid ?? '0') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', isset($pledge) ? $pledge->start_date->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', isset($pledge) ? $pledge->end_date->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" id="status" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            <option value="active" {{ old('status', $pledge->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="completed" {{ old('status', $pledge->status ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="defaulted" {{ old('status', $pledge->status ?? '') == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $pledge->notes ?? '') }}</textarea>
    </div>
</div>
