<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <input type="text" name="description" id="description" value="{{ old('description', $expense->description ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="amount" class="block text-sm font-medium text-gray-700">Amount (Naira)</label>
        <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 sm:text-sm">₦</span>
            </div>
            <input type="number" name="amount" id="amount" value="{{ old('amount', $expense->amount ?? '') }}" required
                   class="pl-8 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="0.00" step="0.01">
        </div>
    </div>
    <div>
        <label for="expense_date" class="block text-sm font-medium text-gray-700">Expense Date</label>
        <input type="date" name="expense_date" id="expense_date" value="{{ old('expense_date', isset($expense) ? $expense->expense_date->format('Y-m-d') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
        <input type="text" name="category" id="category" value="{{ old('category', $expense->category ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
        <select name="department" id="department" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @foreach (['Media', 'Choir', 'Ushers', 'Dance', 'Prayer', 'Lost but Found', 'Drama', 'Sanctuary'] as $department)
                <option value="{{ $department }}" {{ old('department', $expense->department ?? '') == $department ? 'selected' : '' }}>
                    {{ $department }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="budget_id" class="block text-sm font-medium text-gray-700">Budget</label>
        <select name="budget_id" id="budget_id"
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            <option value="">Select Budget (Optional)</option>
            @foreach ($budgets ?? [] as $budget)
                <option value="{{ $budget->id }}" {{ old('budget_id', $expense->budget_id ?? '') == $budget->id ? 'selected' : '' }}>
                    {{ $budget->name }} ({{ $budget->fiscal_year }})
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
        <input type="text" name="payment_method" id="payment_method" value="{{ old('payment_method', $expense->payment_method ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div class="md:col-span-2">
        <label for="receipt_file" class="block text-sm font-medium text-gray-700">Receipt</label>
        <input type="file" name="receipt_file" id="receipt_file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $expense->notes ?? '') }}</textarea>
    </div>
</div>