<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Service Name -->
    <div class="col-span-1 md:col-span-2">
        <label for="name" class="block text-sm font-medium text-gray-700">Service Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $service->name ?? '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               required placeholder="e.g. Sunday Service, Mid-week Service">
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Recurring / One-time Toggle -->
    <div class="col-span-1 md:col-span-2">
        <div class="flex items-center space-x-4">
            <label class="inline-flex items-center">
                <input type="radio" name="is_recurring" value="1" 
                       {{ old('is_recurring', $service->is_recurring ?? true) ? 'checked' : '' }}
                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                       onchange="toggleDateGroup()">
                <span class="ml-2 text-gray-700">Recurring Weekly</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="is_recurring" value="0" 
                       {{ !old('is_recurring', $service->is_recurring ?? true) ? 'checked' : '' }}
                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                       onchange="toggleDateGroup()">
                <span class="ml-2 text-gray-700">One-time Event</span>
            </label>
        </div>
        @error('is_recurring')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Service Date (One-time only) -->
    <div class="col-span-1" id="date-group" style="display: none;">
        <label for="date" class="block text-sm font-medium text-gray-700">Service Date</label>
        <input type="date" name="date" id="date" value="{{ old('date', isset($service->date) ? $service->date->format('Y-m-d') : '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        @error('date')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Day of Week -->
    <div class="col-span-1" id="day-group">
        <label for="day_of_week" class="block text-sm font-medium text-gray-700">Day of Week</label>
        <select name="day_of_week" id="day_of_week" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                <option value="{{ $day }}" {{ old('day_of_week', isset($service) ? strtolower(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$service->day_of_week]) : '') == $day ? 'selected' : '' }}>
                    {{ ucfirst($day) }}
                </option>
            @endforeach
        </select>
        @error('day_of_week')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Start Time -->
    <div class="col-span-1">
        <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
        <input type="time" name="start_time" id="start_time" value="{{ old('start_time', isset($service) ? $service->start_time->format('H:i') : '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               required>
        @error('start_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- End Time -->
    <div class="col-span-1">
        <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
        <input type="time" name="end_time" id="end_time" value="{{ old('end_time', isset($service) ? $service->end_time->format('H:i') : '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               required>
        @error('end_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Location -->
    <div class="col-span-1">
        <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
        <input type="text" name="location" id="location" value="{{ old('location', $service->location ?? '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               placeholder="e.g. Main Auditorium">
        @error('location')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Capacity -->
    <div class="col-span-1">
        <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
        <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $service->capacity ?? '') }}" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               min="1">
        @error('capacity')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Status -->
    <div class="col-span-1">
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" id="status" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="active" {{ old('status', $service->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $service->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Description -->
    <div class="col-span-1 md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
        <textarea name="description" id="description" rows="3" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $service->description ?? '') }}</textarea>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Notes -->
    <div class="col-span-1 md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
        <textarea name="notes" id="notes" rows="3" 
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $service->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
    function toggleDateGroup() {
        const isRecurring = document.querySelector('input[name="is_recurring"]:checked').value === '1';
        const dateGroup = document.getElementById('date-group');
        const dayGroup = document.getElementById('day-group');
        const daySelect = document.getElementById('day_of_week');
        const dateInput = document.getElementById('date');

        if (isRecurring) {
            dateGroup.style.display = 'none';
        } else {
            dateGroup.style.display = 'block';
        }
    }
    
    // Initial call
    document.addEventListener('DOMContentLoaded', toggleDateGroup);

    // Auto-update Day of Week when Date changes
    document.getElementById('date').addEventListener('change', function() {
        const dateVal = this.value;
        if (dateVal) {
            const date = new Date(dateVal);
            const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            const dayName = days[date.getUTCDay()]; 
            
            const daySelect = document.getElementById('day_of_week');
            daySelect.value = dayName;
        }
    });
</script>
