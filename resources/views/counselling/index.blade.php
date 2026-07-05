@extends('layouts.admin')

@section('title', 'Counselling Bookings')
@section('header', 'Counselling Bookings')

@section('content')
<div class="max-w-7xl mx-auto py-6 fade-in">

    <!-- Filters -->
    <div class="glass-effect rounded-2xl p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Booking Requests</h3>
            <p class="text-sm text-gray-500">Review and manage counselling sessions requested via the mobile app.</p>
        </div>
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <form action="{{ route('counselling.index') }}" method="GET" class="flex items-center space-x-2 flex-1 md:flex-none bg-white p-2 rounded-xl shadow-sm border border-gray-100">
                <select name="status" class="rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 text-sm py-2" onchange="this.form.submit()">
                    <option value="" {{ !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="glass-effect rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white/50 divide-y divide-gray-200">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50/50 transition-colors" id="booking-row-{{ $booking->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full object-cover" 
                                         src="{{ $booking->member->profile_photo_url }}" 
                                         alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $booking->member->full_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $booking->member->phone }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $booking->requested_date->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->requested_time)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 line-clamp-2 max-w-xs" title="{{ $booking->reason }}">
                                {{ $booking->reason ?: 'No reason provided' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span id="status-badge-{{ $booking->id }}" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $booking->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $booking->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $booking->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst($booking->status) }}
                            </span>
                            @if($booking->admin_notes)
                                <p class="text-xs text-gray-500 mt-1 max-w-[150px] truncate" title="{{ $booking->admin_notes }}">Note: {{ $booking->admin_notes }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <!-- Only PA can approve/reject. Super Admin can only view. -->
                            @if(Auth::user()->hasPermission('counselling.manage'))
                                @if($booking->status === 'pending')
                                    <button onclick="openActionModal({{ $booking->id }}, 'approved')" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                    <button onclick="openActionModal({{ $booking->id }}, 'rejected')" class="text-red-600 hover:text-red-900">Reject</button>
                                @else
                                    <span class="text-gray-400 text-xs">Actioned</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs" title="Only the PA can manage bookings">View Only</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-gray-500 text-lg font-medium">No booking requests found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bookings->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Action Modal -->
<div id="actionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true" onclick="closeActionModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        <span id="actionTitleText">Action</span> Booking
                    </h3>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500 mb-2">Please provide a note or reason for this action. The member will see this in their app.</p>
                        <textarea id="adminNotesInput" rows="4" class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Type your response here..."></textarea>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <button type="button" id="submitActionBtn" onclick="submitAction()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-primary-600 border border-transparent rounded-md shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="closeActionModal()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentBookingId = null;
    let currentActionStatus = null;

    function openActionModal(bookingId, status) {
        currentBookingId = bookingId;
        currentActionStatus = status;
        
        const modal = document.getElementById('actionModal');
        const titleText = document.getElementById('actionTitleText');
        const submitBtn = document.getElementById('submitActionBtn');
        const notesInput = document.getElementById('adminNotesInput');
        
        notesInput.value = ''; // Reset notes

        if (status === 'approved') {
            titleText.textContent = 'Approve';
            submitBtn.className = 'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm';
        } else {
            titleText.textContent = 'Reject';
            submitBtn.className = 'inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm';
        }

        modal.classList.remove('hidden');
    }

    function closeActionModal() {
        document.getElementById('actionModal').classList.add('hidden');
        currentBookingId = null;
        currentActionStatus = null;
    }

    function submitAction() {
        if (!currentBookingId || !currentActionStatus) return;
        
        const adminNotes = document.getElementById('adminNotesInput').value;
        const submitBtn = document.getElementById('submitActionBtn');
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Processing...';

        fetch(`/counselling/${currentBookingId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: currentActionStatus,
                admin_notes: adminNotes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove hidden class manually to hide modal, reload
                closeActionModal();
                alert(data.message);
                window.location.reload(); 
            } else {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Confirm';
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Confirm';
            alert('An error occurred while updating the status.');
        });
    }
</script>
@endpush
@endsection

