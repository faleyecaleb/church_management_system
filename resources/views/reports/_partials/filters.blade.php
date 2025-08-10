<form method="GET" action="{{ url()->current() }}" class="mb-4 bg-white p-4 rounded-xl border shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700">Start date</label>
            <input type="date" name="start_date" value="{{ request('start_date') ? \Carbon\Carbon::parse(request('start_date'))->format('Y-m-d') : '' }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">End date</label>
            <input type="date" name="end_date" value="{{ request('end_date') ? \Carbon\Carbon::parse(request('end_date'))->format('Y-m-d') : '' }}" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Group by</label>
            <select name="group_by" class="w-full border rounded p-2">
                <option value="day" {{ request('group_by') == 'day' ? 'selected' : '' }}>Day</option>
                <option value="week" {{ request('group_by') == 'week' ? 'selected' : '' }}>Week</option>
                <option value="month" {{ request('group_by') == 'month' ? 'selected' : '' }}>Month</option>
                <option value="year" {{ request('group_by') == 'year' ? 'selected' : '' }}>Year</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Apply</button>
            <a href="{{ url()->current() }}" class="px-4 py-2 bg-gray-100 rounded">Clear</a>
        </div>
    </div>
</form>