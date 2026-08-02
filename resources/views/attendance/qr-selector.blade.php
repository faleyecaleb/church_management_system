@extends('layouts.admin')

@section('header', 'QR Code Service Selector')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{
    selectedMonth: new Date().getMonth() + 1,
    selectedYear: new Date().getFullYear(),
    selectedService: '',
    services: [
        @foreach($services as $service)
        {
            id: {{ $service->id }},
            name: '{{ addslashes($service->name) }}',
            is_recurring: {{ $service->is_recurring ? 'true' : 'false' }},
            day_of_week: '{{ $service->day_of_week_name ?? '' }}',
            start_date: '{{ $service->start_date ? $service->start_date->format('Y-m-d') : '' }}',
            start_date_display: '{{ $service->start_date ? $service->start_date->format('F j, Y') : '' }}',
            start_time: '{{ $service->start_time->format('g:i A') }}',
            end_time: '{{ $service->end_time ? $service->end_time->format('g:i A') : 'TBD' }}',
            location: '{{ addslashes($service->location ?? 'Main Auditorium') }}',
            generation_allowed: {{ $service->isQrGenerationAllowed() ? 'true' : 'false' }}
        },
        @endforeach
    ],
    get filteredServices() {
        return this.services.filter(s => {
            if (s.is_recurring) {
                // Recurring services are active and can occur in any month/year
                return true;
            } else {
                // One-time events are matched exactly against the selected month and year
                if (!s.start_date) return false;
                const parts = s.start_date.split('-');
                if (parts.length >= 2) {
                    const year = parseInt(parts[0], 10);
                    const month = parseInt(parts[1], 10);
                    return month == this.selectedMonth && year == this.selectedYear;
                }
                return false;
            }
        });
    },
    onServiceSelected(serviceId) {
        if (!serviceId) return;
        const selected = this.services.find(s => s.id == serviceId);
        if (selected) {
            if (selected.generation_allowed) {
                // Instantly redirect to generate the QR Code!
                window.location.href = '/attendance/services/' + serviceId + '/qr-code';
            } else {
                // Show an alert if outside the 24 hour liberty time
                alert('🔒 QR Generation is locked. You can only generate and print QR codes up to 24 hours in advance of the service start time.');
                this.selectedService = ''; // Reset selection
            }
        }
    }
}">
    <div class="max-w-2xl mx-auto">
        <!-- Error alert if session has error -->
        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded text-red-700 flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-8 shadow-xl">
            <div class="text-center border-b border-gray-100 pb-6 mb-8">
                <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-indigo-100/50 shadow-sm">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Instant QR Code Generator</h2>
                <p class="text-sm text-gray-500 mt-1">Select the Year, Month, and Service to instantly generate and print your church check-in QR code.</p>
            </div>

            <!-- Instant Selector Dropdown Fields Form -->
            <div class="space-y-6 bg-gray-50/50 p-6 rounded-2xl border border-gray-100/80 shadow-inner">
                <!-- Dropdown 1: Year -->
                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2 tracking-wider">1. Select Year</label>
                    <select x-model="selectedYear" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 font-semibold shadow-sm transition-all">
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                        <option value="2027">2027</option>
                        <option value="2028">2028</option>
                    </select>
                </div>

                <!-- Dropdown 2: Month -->
                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2 tracking-wider">2. Select Month</label>
                    <select x-model="selectedMonth" class="w-full bg-white border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 font-semibold shadow-sm transition-all">
                        <option value="1">January</option>
                        <option value="2">February</option>
                        <option value="3">March</option>
                        <option value="4">April</option>
                        <option value="5">May</option>
                        <option value="6">June</option>
                        <option value="7">July</option>
                        <option value="8">August</option>
                        <option value="9">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>

                <!-- Dropdown 3: Service -->
                <div>
                    <label class="block text-xs font-extrabold text-gray-500 uppercase mb-2 tracking-wider">3. Select Service to Generate</label>
                    <select x-model="selectedService" @change="onServiceSelected($event.target.value)" class="w-full bg-white border border-gray-200 rounded-xl py-3.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 font-bold shadow-sm transition-all cursor-pointer">
                        <option value="">-- Choose Service (Triggers Generation) --</option>
                        <template x-for="service in filteredServices" :key="service.id">
                            <option :value="service.id" x-text="service.name + ' (' + (service.is_recurring ? 'Every ' + service.day_of_week : service.start_date_display) + ' @ ' + service.start_time + ')' "></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Helpful UX Instructions -->
            <div class="mt-6 flex items-start p-4 bg-indigo-50/50 rounded-xl border border-indigo-100/50">
                <span class="text-lg mr-3 mt-0.5">ℹ️</span>
                <div class="text-xs text-indigo-700 leading-relaxed">
                    <p class="font-bold mb-1">Instant Generation Rules:</p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Selecting a service above will **instantly** compile and load your printable QR sheet.</li>
                        <li>QR codes can be pre-printed and prepared up to **24 hours in advance** of the service.</li>
                        <li>For recurring services, the secure token is dynamically calculated for the next upcoming week automatically.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection