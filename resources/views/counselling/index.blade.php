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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <!-- Only PA can approve/reject. Super Admin can only view. -->
                            @if(Auth::user()->hasPermission('counselling.manage'))
                                @if($booking->status === 'pending')
                                    <button onclick="updateStatus({{ $booking->id }}, 'approved')" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                                    <button onclick="updateStatus({{ $booking->id }}, 'rejected')" class="text-red-600 hover:text-red-900">Reject</button>
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

@push('scripts')
<script>
    function updateStatus(bookingId, status) {
        if (!confirm('Are you sure you want to mark this booking as ' + status + '?')) {
            return;
        }

        fetch(`/counselling/${bookingId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload(); // Reload to reflect changes and remove action buttons
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    }
</script>
@endpush
@endsection

