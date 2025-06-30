@extends('layouts.admin')

@section('title', $prayerRequest->title)
@section('header', 'Prayer Request Details')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $prayerRequest->title }}</h1>
                <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                    <span>
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ $prayerRequest->member ? $prayerRequest->member->full_name : 'Anonymous' }}
                    </span>
                    <span>
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $prayerRequest->created_at->format('F j, Y \a\t g:i A') }}
                    </span>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('prayer-requests.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to List
                </a>
                
                @if($prayerRequest->status === 'active')
                    <button onclick="prayForRequest({{ $prayerRequest->id }})" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        Pray for This
                    </button>
                @endif

                @can('update', $prayerRequest)
                    <a href="{{ route('prayer-requests.edit', $prayerRequest) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Prayer Request Details -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Prayer Request</h2>
                <div class="prose prose-gray max-w-none">
                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $prayerRequest->description }}</p>
                </div>
            </div>

            <!-- Recent Prayers -->
            @if($prayerRequest->prayers && $prayerRequest->prayers->count() > 0)
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Prayers</h2>
                    <div class="space-y-4">
                        @foreach($prayerRequest->prayers->take(5) as $prayer)
                            <div class="border-l-4 border-purple-400 bg-purple-50 pl-4 py-3 rounded-r-lg">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-purple-800">
                                            {{ $prayer->prayed_by ? $prayer->prayed_by->name : 'Anonymous' }}
                                        </p>
                                        <p class="text-xs text-purple-600">
                                            {{ $prayer->created_at->diffForHumans() }}
                                        </p>
                                        @if($prayer->notes)
                                            <p class="text-sm text-purple-700 mt-2">{{ $prayer->notes }}</p>
                                        @endif
                                    </div>
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($prayerRequest->prayers->count() > 5)
                            <p class="text-sm text-gray-500 text-center">
                                And {{ $prayerRequest->prayers->count() - 5 }} more prayers...
                            </p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status and Info -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Info</h3>
                <div class="space-y-4">
                    <!-- Status -->
                    <div>
                        <label class="text-sm font-medium text-gray-600">Status</label>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ $prayerRequest->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $prayerRequest->status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $prayerRequest->status === 'archived' ? 'bg-gray-100 text-gray-800' : '' }}">
                                {{ ucfirst($prayerRequest->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Privacy -->
                    <div>
                        <label class="text-sm font-medium text-gray-600">Privacy</label>
                        <div class="mt-1">
                            @if($prayerRequest->is_private ?? false)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Private
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Public
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Days in Prayer -->
                    <div>
                        <label class="text-sm font-medium text-gray-600">Days in Prayer</label>
                        <p class="text-lg font-semibold text-gray-900">{{ $prayerRequest->created_at->diffInDays(now()) }}</p>
                    </div>
                </div>
            </div>

            <!-- Prayer Statistics -->
            <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Prayer Statistics</h3>
                <div class="space-y-4">
                    <!-- Total Prayers -->
                    <div class="text-center">
                        <div class="text-3xl font-bold text-purple-600">{{ $prayerRequest->prayer_count ?? 0 }}</div>
                        <div class="text-sm text-gray-600">Total Prayers</div>
                    </div>

                    <!-- Last Prayed -->
                    @if($prayerRequest->last_prayed_at)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Last Prayed</label>
                            <p class="text-sm text-gray-900">{{ $prayerRequest->last_prayed_at->diffForHumans() }}</p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <svg class="w-8 h-8 text-orange-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-sm text-orange-600 font-medium">Needs Prayer</p>
                            <p class="text-xs text-gray-500">No one has prayed yet</p>
                        </div>
                    @endif

                    <!-- Prayer Target Progress -->
                    @if($prayerRequest->prayer_target)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Prayer Goal Progress</label>
                            <div class="mt-2">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>{{ $prayerRequest->prayer_count ?? 0 }} / {{ $prayerRequest->prayer_target }}</span>
                                    <span>{{ round((($prayerRequest->prayer_count ?? 0) / $prayerRequest->prayer_target) * 100) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full" 
                                         style="width: {{ min(100, round((($prayerRequest->prayer_count ?? 0) / $prayerRequest->prayer_target) * 100)) }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            @can('update', $prayerRequest)
                <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        @if($prayerRequest->status === 'active')
                            <form action="{{ route('prayer-requests.complete', $prayerRequest) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                        onclick="return confirm('Mark this prayer request as completed?')">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Mark as Completed
                                </button>
                            </form>

                            <form action="{{ route('prayer-requests.archive', $prayerRequest) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors"
                                        onclick="return confirm('Archive this prayer request?')">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6 6-6"></path>
                                    </svg>
                                    Archive Request
                                </button>
                            </form>
                        @elseif($prayerRequest->status === 'archived')
                            <form action="{{ route('prayer-requests.reactivate', $prayerRequest) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5"></path>
                                    </svg>
                                    Reactivate Request
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>

<!-- Prayer Modal -->
<div id="prayerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Record Your Prayer</h3>
            <form id="prayerForm" method="POST" action="{{ route('prayer-requests.pray', $prayerRequest) }}">
                @csrf
                <div class="mb-4">
                    <label for="prayer_notes" class="block text-sm font-medium text-gray-700 mb-2">Prayer Notes (Optional)</label>
                    <textarea name="notes" 
                              id="prayer_notes" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Share any thoughts or reflections from your prayer..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closePrayerModal()"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Record Prayer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function prayForRequest(requestId) {
    const modal = document.getElementById('prayerModal');
    modal.classList.remove('hidden');
}

function closePrayerModal() {
    const modal = document.getElementById('prayerModal');
    modal.classList.add('hidden');
    document.getElementById('prayer_notes').value = '';
}

// Close modal when clicking outside
document.getElementById('prayerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePrayerModal();
    }
});
</script>
@endpush
@endsection