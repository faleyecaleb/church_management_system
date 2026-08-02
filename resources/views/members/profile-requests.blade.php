@extends('layouts.admin')

@section('header', 'Profile Update Requests')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ showRejectModal: false, activeRequestId: null }">
    <div class="max-w-5xl mx-auto">
        <!-- Success/Error alerts -->
        @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-xl text-green-700 flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-xl text-red-700 flex items-center justify-between shadow-sm">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        @endif

        <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-xl mb-8">
            <div class="border-b border-gray-100 pb-5 mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Member Profile Update Queue</h2>
                <p class="text-sm text-gray-500 mt-1">Review pending profile change submissions sent by mobile app users. Approving a request will automatically update their profile.</p>
            </div>

            @if($requests->isEmpty())
            <div class="py-12 text-center bg-gray-50 border border-dashed border-gray-200 rounded-2xl">
                <span class="text-4xl">🎉</span>
                <h4 class="text-gray-800 font-bold mt-3">All Caught Up!</h4>
                <p class="text-xs text-gray-500 mt-1">There are no pending profile update requests to review.</p>
            </div>
            @else
            <div class="space-y-8">
                @foreach($requests as $req)
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-md p-6 hover:shadow-lg transition-all">
                    <!-- Request Header -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-50 pb-4 mb-4">
                        <div class="flex items-center space-x-4">
                            <img class="h-12 w-12 rounded-full object-cover border-2 border-indigo-50 shadow-sm" src="{{ $req->member->profile_photo_url }}" alt="{{ $req->member->full_name }}">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $req->member->full_name }}</h3>
                                <p class="text-xs text-gray-400">Submitted {{ $req->created_at->diffForHumans() }} ({{ $req->created_at->format('M d, Y @ h:i A') }})</p>
                            </div>
                        </div>
                        <div class="mt-3 sm:mt-0 px-3 py-1 bg-amber-50 border border-amber-200 rounded-full text-amber-700 text-xs font-bold uppercase tracking-wider">
                            ● Pending Approval
                        </div>
                    </div>

                    <!-- Comparison Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Left: Current Details -->
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <h4 class="text-xs font-extrabold text-gray-400 uppercase tracking-widest mb-3">Current Details</h4>
                            <div class="space-y-2 text-sm">
                                @foreach($req->requested_data as $key => $newValue)
                                <div class="flex justify-between py-1 border-b border-gray-100 last:border-0">
                                    <span class="font-semibold text-gray-500 uppercase text-xs">{{ str_replace('_', ' ', $key) }}:</span>
                                    <span class="text-gray-900 font-medium">{{ $req->member->{$key} ?? 'N/A' }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Right: Requested Changes -->
                        <div class="bg-indigo-50/30 rounded-xl p-4 border border-indigo-100/40">
                            <h4 class="text-xs font-extrabold text-indigo-400 uppercase tracking-widest mb-3">Requested Changes</h4>
                            <div class="space-y-2 text-sm">
                                @foreach($req->requested_data as $key => $newValue)
                                <div class="flex justify-between py-1 border-b border-indigo-100/20 last:border-0">
                                    <span class="font-semibold text-indigo-500 uppercase text-xs">{{ str_replace('_', ' ', $key) }}:</span>
                                    <span class="text-indigo-900 font-bold bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100/50">{{ $newValue }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Action Row -->
                    <div class="flex justify-end space-x-3 border-t border-gray-50 pt-4">
                        <!-- Reject Button triggers Alpine modal -->
                        <button type="button" @click="activeRequestId = {{ $req->id }}; showRejectModal = true" class="px-5 py-2 border border-red-200 hover:bg-red-50 text-red-600 font-bold text-sm rounded-xl transition-colors shadow-sm">
                            Decline Update
                        </button>
                        
                        <!-- Approve Form -->
                        <form action="{{ route('members.profile-requests.approve', $req->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to merge these updates directly into the member profile?');">
                            @csrf
                            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl transition-colors shadow-sm flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Approve & Merge
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Alpine.js Decline Request Modal -->
    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" style="display: none;" x-transition>
        <div class="bg-white rounded-2xl border border-gray-200 shadow-2xl max-w-md w-full p-6" @click.away="showRejectModal = false">
            <h3 class="text-xl font-bold text-gray-900 mb-2">Decline Profile Update</h3>
            <p class="text-sm text-gray-500 mb-4">Please provide an optional reason/notes for declining this update request. This will be logged on the system.</p>

            <form :action="'/members/profile-requests/' + activeRequestId + '/reject'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-2">Decline Reason</label>
                    <textarea name="admin_notes" rows="3" class="w-full bg-gray-50 border border-gray-200 rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-red-500 text-gray-700 font-medium" placeholder="E.g. Address is incomplete, Phone number format is invalid..."></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 border border-gray-200 hover:bg-gray-50 text-gray-600 font-semibold text-sm rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-lg transition-colors shadow-md">
                        Decline Now
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
