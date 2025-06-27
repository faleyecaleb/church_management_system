<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Service Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $service->description ?? '') }}</textarea>
    </div>
    <div>
        <label for="day_of_week" class="block text-sm font-medium text-gray-700">Day of the Week</label>
        <select name="day_of_week" id="day_of_week" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            @foreach (['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                <option value="{{ $day }}" {{ old('day_of_week', $service->day_of_week ?? '') == $day ? 'selected' : '' }}>
                    {{ ucfirst($day) }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
        <input type="text" name="location" id="location" value="{{ old('location', $service->location ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
        <input type="time" name="start_time" id="start_time" value="{{ old('start_time', isset($service) ? $service->start_time->format('H:i') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
        <input type="time" name="end_time" id="end_time" value="{{ old('end_time', isset($service) ? $service->end_time->format('H:i') : '') }}" required
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
        <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $service->capacity ?? '') }}"
               class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" id="status" required
                class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            <option value="active" {{ old('status', $service->status ?? '') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $service->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="md:col-span-2">
        <label for="is_recurring" class="flex items-center">
            <input type="hidden" name="is_recurring" value="0">
            <input type="checkbox" name="is_recurring" id="is_recurring" value="1" {{ old('is_recurring', $service->is_recurring ?? false) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-gray-600">This is a recurring service</span>
        </label>
    </div>
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" id="notes" rows="3"
                  class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('notes', $service->notes ?? '') }}</textarea>
    </div>
</div>
