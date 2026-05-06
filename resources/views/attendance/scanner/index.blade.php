@extends('layouts.admin')

@section('title', 'Fast Check-In Scanner')
@section('header', 'Fast Scanner Check-In')

@section('content')
<div class="max-w-4xl mx-auto py-6 fade-in">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden glass-effect">
        <div class="p-8 border-b border-gray-100 bg-gray-50/50">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Scanner / Manual Check-In</h2>
                    <p class="text-sm text-gray-500 mt-1">For members without smartphones. Use a barcode scanner, fingerprint reader, or type their Phone Number.</p>
                </div>
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center text-primary-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2m0 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Service Selection -->
            <div class="mb-6">
                <label for="service_id" class="block text-sm font-semibold text-gray-700 mb-2">Select Active Service</label>
                <select id="service_id" class="w-full lg:w-1/2 rounded-xl border-gray-300 focus:ring-primary-500 focus:border-primary-500 shadow-sm text-lg py-3">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }} ({{ \Carbon\Carbon::parse($service->start_time)->format('h:i A') }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="p-8">
            <!-- Scanner Input Area -->
            <div class="relative">
                <label for="scan_input" class="block text-sm font-semibold text-gray-700 mb-2">Scan ID or Enter Phone Number</label>
                <div class="relative flex items-center">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" id="scan_input" autocomplete="off" autofocus
                           class="block w-full pl-12 pr-4 py-4 sm:text-lg border-gray-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 bg-gray-50 shadow-inner"
                           placeholder="Waiting for scanner input...">
                    <button id="manual_submit" class="absolute inset-y-2 right-2 px-6 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        Check In
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500">Ensure the input field is focused if using a hardware scanner. The scanner will automatically submit upon reading.</p>
            </div>

            <!-- Feedback Area -->
            <div id="feedback_area" class="mt-8 hidden">
                <div id="feedback_alert" class="rounded-xl p-6 border-l-4 transform transition-all duration-300 scale-95 opacity-0">
                    <div class="flex items-start">
                        <div class="flex-shrink-0" id="feedback_icon">
                            <!-- Icon injected via JS -->
                        </div>
                        <div class="ml-4 w-full">
                            <h3 class="text-lg font-bold" id="feedback_title"></h3>
                            <div class="mt-1 text-sm" id="feedback_message"></div>
                            
                            <!-- Member Quick Profile Card (Hidden by default) -->
                            <div id="member_card" class="mt-4 bg-white/50 rounded-lg p-4 flex items-center space-x-4 hidden border border-gray-100">
                                <img id="member_photo" src="" alt="Profile" class="w-12 h-12 rounded-full object-cover shadow-sm">
                                <div>
                                    <p id="member_name" class="font-bold text-gray-900"></p>
                                    <p id="member_phone" class="text-xs text-gray-500"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Scans Log (Optional Visual Feedback) -->
            <div class="mt-12">
                <h4 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Recent Scans</h4>
                <ul id="scan_log" class="space-y-2 max-h-48 overflow-y-auto">
                    <!-- Logs injected via JS -->
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputField = document.getElementById('scan_input');
        const serviceSelect = document.getElementById('service_id');
        const manualSubmitBtn = document.getElementById('manual_submit');
        const feedbackArea = document.getElementById('feedback_area');
        const feedbackAlert = document.getElementById('feedback_alert');
        const feedbackIcon = document.getElementById('feedback_icon');
        const feedbackTitle = document.getElementById('feedback_title');
        const feedbackMessage = document.getElementById('feedback_message');
        const memberCard = document.getElementById('member_card');
        const scanLog = document.getElementById('scan_log');

        // Keep focus on input for scanners
        document.body.addEventListener('click', (e) => {
            if(e.target.id !== 'service_id' && e.target.id !== 'manual_submit') {
                inputField.focus();
            }
        });

        // Trigger on Enter key (standard scanner behavior)
        inputField.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                processScan();
            }
        });

        manualSubmitBtn.addEventListener('click', processScan);

        function processScan() {
            const inputValue = inputField.value.trim();
            const serviceId = serviceSelect.value;

            if (!inputValue) return;
            if (!serviceId) {
                showFeedback('error', 'Configuration Error', 'Please select a service first.');
                return;
            }

            // Disable input briefly
            inputField.disabled = true;
            manualSubmitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch('{{ route("attendance.scanner.process") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    scan_input: inputValue,
                    service_id: serviceId
                })
            })
            .then(response => response.json())
            .then(data => {
                inputField.value = '';
                if (data.success) {
                    if (data.status === 'already_checked_in') {
                        showFeedback('warning', 'Already Checked In', data.message, data.member);
                        addLog('warning', data.message);
                    } else {
                        showFeedback('success', 'Check-In Successful', data.message, data.member);
                        addLog('success', data.message);
                    }
                } else {
                    showFeedback('error', 'Scan Failed', data.message);
                    addLog('error', data.message);
                }
            })
            .catch(error => {
                inputField.value = '';
                showFeedback('error', 'System Error', 'Could not process the scan. Check connection.');
            })
            .finally(() => {
                inputField.disabled = false;
                inputField.focus();
                manualSubmitBtn.innerHTML = 'Check In';
            });
        }

        function showFeedback(type, title, message, member = null) {
            feedbackArea.classList.remove('hidden');
            
            // Reset classes
            feedbackAlert.className = 'rounded-xl p-6 border-l-4 transform transition-all duration-300 scale-100 opacity-100';
            
            if (type === 'success') {
                feedbackAlert.classList.add('bg-green-50', 'border-green-500', 'text-green-800');
                feedbackIcon.innerHTML = '<svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            } else if (type === 'error') {
                feedbackAlert.classList.add('bg-red-50', 'border-red-500', 'text-red-800');
                feedbackIcon.innerHTML = '<svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            } else if (type === 'warning') {
                feedbackAlert.classList.add('bg-yellow-50', 'border-yellow-500', 'text-yellow-800');
                feedbackIcon.innerHTML = '<svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
            }

            feedbackTitle.textContent = title;
            feedbackMessage.textContent = message;

            if (member) {
                memberCard.classList.remove('hidden');
                document.getElementById('member_name').textContent = `${member.first_name} ${member.last_name}`;
                document.getElementById('member_phone').textContent = member.phone || 'No phone recorded';
                // Fallback avatar if empty
                document.getElementById('member_photo').src = member.profile_photo_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(member.first_name + ' ' + member.last_name)}&background=random`;
            } else {
                memberCard.classList.add('hidden');
            }
        }

        function addLog(type, message) {
            const li = document.createElement('li');
            li.className = 'text-sm flex items-center space-x-2 py-1';
            
            const time = new Date().toLocaleTimeString();
            let dotColor = 'bg-gray-400';
            if(type === 'success') dotColor = 'bg-green-500';
            if(type === 'warning') dotColor = 'bg-yellow-500';
            if(type === 'error') dotColor = 'bg-red-500';

            li.innerHTML = `<span class="w-2 h-2 rounded-full ${dotColor}"></span> <span class="text-gray-400 font-mono text-xs">[${time}]</span> <span class="text-gray-700">${message}</span>`;
            
            scanLog.insertBefore(li, scanLog.firstChild);
            
            // Keep only last 10
            if (scanLog.children.length > 10) {
                scanLog.removeChild(scanLog.lastChild);
            }
        }
    });
</script>
@endpush
@endsection
