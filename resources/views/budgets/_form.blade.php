<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Budget Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $budget->name ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700">Amount (Naira)</label>
        <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">₦</span>
            </div>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $budget->amount ?? '') }}" required
                   class="pl-8 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01">
        </div>
    </div>
    <div>
        <label for="fiscal_year" class="block text-sm font-medium text-gray-700">Fiscal Year</label>
        <input type="number" name="fiscal_year" id="fiscal_year" value="{{ old('fiscal_year', $budget->fiscal_year ?? now()->year) }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
        <input type="text" name="category" id="category" value="{{ old('category', $budget->category ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
        <select name="department" id="department" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @foreach (['Media', 'Choir', 'Ushers', 'Dance', 'Prayer', 'Lost but Found', 'Drama', 'Sanctuary'] as $department)
                <option value="{{ $department }}" {{ old('department', $budget->department ?? '') == $department ? 'selected' : '' }}>
                    {{ $department }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', isset($budget) ? $budget->start_date->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', isset($budget) ? $budget->end_date->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $budget->notes ?? '') }}</textarea>
    </div>
    <div class="md:col-span-2">
        <label for="is_active" class="flex items-center">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $budget->is_active ?? false) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-gray-600">Active</span>
        </label>
    </div>
</div>