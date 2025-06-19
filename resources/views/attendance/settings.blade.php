@extends('layouts.admin')

@section('title', 'Attendance Settings')
@section('header', 'Attendance & Check-in Settings')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('attendance.settings.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- QR Code Settings -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">QR Code Settings</h3>
                    <span class="text-sm text-gray-500">Configure QR code behavior</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- QR Code Expiry -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">QR Code Expiry Window</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="qr_expiry_before" class="block text-xs text-gray-500 mb-1">Minutes Before Service</label>
                                <input type="number" name="qr_expiry_before" id="qr_expiry_before" 
                                    value="{{ old('qr_expiry_before', config('attendance.qr_expiry_before', 15)) }}" 
                                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow"
                                    min="0" max="120">
                            </div>
                            <div>
                                <label for="qr_expiry_after" class="block text-xs text-gray-500 mb-1">Minutes After Service</label>
                                <input type="number" name="qr_expiry_after" id="qr_expiry_after" 
                                    value="{{ old('qr_expiry_after', config('attendance.qr_expiry_after', 15)) }}" 
                                    class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow"
                                    min="0" max="120">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500">Set how long the QR code remains valid before and after the service start time.</p>
                    </div>

                    <!-- Mobile Check-in Settings -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">Mobile Check-in Settings</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="enable_mobile_checkin" id="enable_mobile_checkin" 
                                    class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" 
                                    {{ config('attendance.enable_mobile_checkin', true) ? 'checked' : '' }}>
                                <label for="enable_mobile_checkin" class="ml-2 block text-sm text-gray-700">
                                    Enable Mobile Check-in
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="require_geofencing" id="require_geofencing" 
                                    class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                    {{ config('attendance.require_geofencing', false) ? 'checked' : '' }}>
                                <label for="require_geofencing" class="ml-2 block text-sm text-gray-700">
                                    Require Location Verification
                                </label>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="allowed_distance" class="block text-xs text-gray-500">Maximum Distance from Church (meters)</label>
                            <input type="number" name="allowed_distance" id="allowed_distance" 
                                value="{{ old('allowed_distance', config('attendance.allowed_distance', 100)) }}" 
                                class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow"
                                min="10" max="1000">
                            <p class="text-xs text-gray-500">Only applies if location verification is enabled.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Church Location Settings -->
            <div class="space-y-6 pt-6 border-t">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Church Location</h3>
                    <span class="text-sm text-gray-500">Set church coordinates for geofencing</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="church_latitude" class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                        <input type="text" name="church_latitude" id="church_latitude" 
                            value="{{ old('church_latitude', config('attendance.church_latitude')) }}" 
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow"
                            placeholder="e.g. -1.2921">
                    </div>
                    <div>
                        <label for="church_longitude" class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                        <input type="text" name="church_longitude" id="church_longitude" 
                            value="{{ old('church_longitude', config('attendance.church_longitude')) }}" 
                            class="w-full rounded-xl border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-shadow"
                            placeholder="e.g. 36.8219">
                    </div>
                </div>
                <div id="map" class="h-64 w-full rounded-xl border border-gray-200 bg-gray-50"></div>
                <p class="text-xs text-gray-500">Click on the map to set the church location coordinates.</p>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = new google.maps.Map(document.getElementById('map'), {
            zoom: 15,
            center: { 
                lat: parseFloat(document.getElementById('church_latitude').value) || -1.2921,
                lng: parseFloat(document.getElementById('church_longitude').value) || 36.8219
            }
        });

        let marker = new google.maps.Marker({
            position: map.getCenter(),
            map: map,
            draggable: true
        });

        map.addListener('click', function(e) {
            marker.setPosition(e.latLng);
            document.getElementById('church_latitude').value = e.latLng.lat();
            document.getElementById('church_longitude').value = e.latLng.lng();
        });

        marker.addListener('dragend', function(e) {
            document.getElementById('church_latitude').value = e.latLng.lat();
            document.getElementById('church_longitude').value = e.latLng.lng();
        });
    });
</script>
@endpush
@endsection