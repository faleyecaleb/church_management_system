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
                       onchange="handleRecurringChange()">
                <span class="ml-2 text-gray-700">Recurring Weekly</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="is_recurring" value="0" 
                       {{ !old('is_recurring', $service->is_recurring ?? true) ? 'checked' : '' }}
                       class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out"
                       onchange="handleRecurringChange()">
                <span class="ml-2 text-gray-700">One-time Event</span>
            </label>
        </div>
        @error('is_recurring')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Day of Week -->
    <div class="col-span-1">
        <label for="day_of_week" class="block text-sm font-medium text-gray-700">Day of Week</label>
        <select name="day_of_week" id="day_of_week" onchange="generateDates()"
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

    <!-- Service Date (One-time only) -->
    <div class="col-span-1" id="date-group" style="display: none;">
        <label for="date" class="block text-sm font-medium text-gray-700">Select Date</label>
        <select name="date" id="date" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
               <!-- Options populated by JS -->
        </select>
        @error('date')
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
    const existingDate = "{{ old('date', isset($service->date) ? $service->date->format('Y-m-d') : '') }}";

    function handleRecurringChange() {
        const isRecurring = document.querySelector('input[name="is_recurring"]:checked').value === '1';
        const dateGroup = document.getElementById('date-group');
        
        if (isRecurring) {
            dateGroup.style.display = 'none';
        } else {
            dateGroup.style.display = 'block';
            generateDates();
        }
    }

    function generateDates() {
        const daySelect = document.getElementById('day_of_week');
        const dateSelect = document.getElementById('date');
        const selectedDay = daySelect.value;
        const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        const targetDayIndex = days.indexOf(selectedDay);
        
        dateSelect.innerHTML = ''; // Clear existing options
        
        // Start from today
        let currentDate = new Date();
        // Reset time to ensure safe comparisons
        currentDate.setHours(0,0,0,0);
        
        // Find next occurrence of target day
        while (currentDate.getDay() !== targetDayIndex) {
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // Generate dates for the rest of the current month
        const currentMonth = new Date().getMonth();
        
        while (currentDate.getMonth() === currentMonth) {
            const option = document.createElement('option');
            // Format YYYY-MM-DD for value
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;
            
            // Format friendly name: "Friday, 4th January 2026"
            const friendlyName = currentDate.toLocaleDateString('en-GB', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });

            option.value = dateStr;
            option.text = friendlyName;
            
            if (dateStr === existingDate) {
                option.selected = true;
            }
            
            dateSelect.appendChild(option);
            
            // Increment by 1 week
            currentDate.setDate(currentDate.getDate() + 7);
        }

        // Ensure the existing date is added if it was outside the current month (e.g. editing a future/past event)
        if (existingDate && !Array.from(dateSelect.options).some(opt => opt.value === existingDate)) {
             const existingDateObj = new Date(existingDate);
             const option = document.createElement('option');
             option.value = existingDate;
             option.text = existingDateObj.toLocaleDateString('en-GB', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            }) + " (Original Date)";
             option.selected = true;
             dateSelect.appendChild(option);
        }
    }
    
    // Initial call
    document.addEventListener('DOMContentLoaded', () => {
        handleRecurringChange();
        // If we have an existing date, ensure we populate the dropdown so it can be selected
        if (existingDate) {
             generateDates();
        }
    });

</script>
