@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Status Details</h2>
                <p class="text-gray-600">{{ $member->name }}</p>
            </div>
            <div class="space-x-2">
                @if($status->isCurrent())
                    <a href="{{ route('membership.status.edit', [$member, $status]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Status
                    </a>
                @endif
                <a href="{{ route('membership.status.index', $member) }}" class="text-gray-600 hover:text-gray-800">
                    Back to Status History
                </a>
            </div>
        </div>

        <!-- Status Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Main Status Details -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Status Information</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ 
                                match($status->status) {
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-red-100 text-red-800',
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'transferred' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }
                            }}">
                                {{ $availableStatuses[$status->status] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500">Start Date</p>
                                <p class="font-medium">{{ $status->start_date->format('M d, Y g:i A') }}</p>
                            </div>

                            @if($status->end_date)
                            <div>
                                <p class="text-gray-500">End Date</p>
                                <p class="font-medium">{{ $status->end_date->format('M d, Y g:i A') }}</p>
                            </div>
                            @endif

                            <div>
                                <p class="text-gray-500">Changed By</p>
                                <p class="font-medium">{{ $status->changedBy->name }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500">Duration</p>
                                <p class="font-medium">
                                    {{ $status->getDurationInDays() }} days
                                    @if($status->isCurrent())
                                        (Current)
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($status->notes)
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Notes</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $status->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Additional Details -->
            <div class="space-y-6">
                <!-- Membership Class -->
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Membership Details</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            @if($status->class_completed)
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-green-700">Membership Class Completed</span>
                            @else
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <span class="text-gray-500">Membership Class Not Completed</span>
                            @endif
                        </div>

                        @if($status->renewal_date)
                        <div>
                            <p class="text-gray-500 mb-1">Renewal Date</p>
                            <p class="font-medium {{ $status->needsRenewal() ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $status->renewal_date->format('M d, Y') }}
                                @if($status->needsRenewal())
                                    <span class="text-sm ml-2">(Renewal Required)</span>
                                @endif
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Transfer Information -->
                @if($status->status === 'transferred')
                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Transfer Information</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-500 mb-1">Transferred To</p>
                            <p class="font-medium text-gray-700">{{ $status->transfer_church }}</p>
                        </div>

                        @if($status->transfer_date)
                        <div>
                            <p class="text-gray-500 mb-1">Transfer Date</p>
                            <p class="font-medium text-gray-700">{{ $status->transfer_date->format('M d, Y') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection