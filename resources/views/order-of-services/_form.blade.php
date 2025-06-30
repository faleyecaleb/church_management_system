@if ($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Program Name -->
    <div class="md:col-span-2">
        <label for="program" class="block text-sm font-medium text-gray-700 mb-2">Program Name *</label>
        <input type="text" 
               name="program" 
               id="program" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('program', isset($orderOfService) ? $orderOfService->program : '') }}" 
               required
               placeholder="e.g., Opening Prayer, Worship Songs, Sermon">
    </div>

    <!-- Order -->
    <div>
        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Order</label>
        <input type="number" 
               name="order" 
               id="order" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('order', isset($orderOfService) ? $orderOfService->order : '') }}"
               min="1"
               placeholder="Leave blank to auto-assign">
        <p class="mt-1 text-xs text-gray-500">Leave blank to automatically assign the next order number</p>
    </div>

    <!-- Leader -->
    <div>
        <label for="leader" class="block text-sm font-medium text-gray-700 mb-2">Leader/Presenter</label>
        <input type="text" 
               name="leader" 
               id="leader" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('leader', isset($orderOfService) ? $orderOfService->leader : '') }}"
               placeholder="e.g., Pastor John, Choir Director">
    </div>

    <!-- Start Time -->
    <div>
        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
        <input type="time" 
               name="start_time" 
               id="start_time" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('start_time', isset($orderOfService) && $orderOfService->start_time ? $orderOfService->start_time->format('H:i') : '') }}">
    </div>

    <!-- End Time -->
    <div>
        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
        <input type="time" 
               name="end_time" 
               id="end_time" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('end_time', isset($orderOfService) && $orderOfService->end_time ? $orderOfService->end_time->format('H:i') : '') }}">
    </div>

    <!-- Duration (Alternative to End Time) -->
    <div>
        <label for="duration_minutes" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
        <input type="number" 
               name="duration_minutes" 
               id="duration_minutes" 
               class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
               value="{{ old('duration_minutes', isset($orderOfService) ? $orderOfService->duration_minutes : '') }}"
               min="1"
               max="480"
               placeholder="e.g., 15">
        <p class="mt-1 text-xs text-gray-500">Use this if you don't have specific start/end times</p>
    </div>

    <!-- Description -->
    <div class="md:col-span-2">
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
        <textarea name="description" 
                  id="description" 
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Brief description of this program item...">{{ old('description', isset($orderOfService) ? $orderOfService->description : '') }}</textarea>
    </div>

    <!-- Notes -->
    <div class="md:col-span-2">
        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
        <textarea name="notes" 
                  id="notes" 
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Any special notes or instructions...">{{ old('notes', isset($orderOfService) ? $orderOfService->notes : '') }}</textarea>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    const durationInput = document.getElementById('duration_minutes');

    // Auto-calculate duration when start and end times are set
    function calculateDuration() {
        if (startTimeInput.value && endTimeInput.value) {
            const start = new Date('2000-01-01 ' + startTimeInput.value);
            const end = new Date('2000-01-01 ' + endTimeInput.value);
            const diffMs = end - start;
            const diffMins = Math.round(diffMs / 60000);
            
            if (diffMins > 0) {
                durationInput.value = diffMins;
            }
        }
    }

    startTimeInput.addEventListener('change', calculateDuration);
    endTimeInput.addEventListener('change', calculateDuration);

    // Auto-calculate end time when start time and duration are set
    durationInput.addEventListener('input', function() {
        if (startTimeInput.value && durationInput.value) {
            const start = new Date('2000-01-01 ' + startTimeInput.value);
            start.setMinutes(start.getMinutes() + parseInt(durationInput.value));
            
            const hours = start.getHours().toString().padStart(2, '0');
            const minutes = start.getMinutes().toString().padStart(2, '0');
            endTimeInput.value = hours + ':' + minutes;
        }
    });
});
</script>
