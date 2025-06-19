@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Membership Status History</h2>
                <p class="text-gray-600">{{ $member->name }}</p>
            </div>
            <div class="space-x-2">
                @can('manage members')
                <a href="{{ route('membership.status.create', $member) }}" 
                   class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Change Status
                </a>
                @endcan
                <a href="{{ route('members.show', $member) }}" class="text-gray-600 hover:text-gray-800">
                    Back to Member
                </a>
            </div>
        </div>

        <!-- Current Status Card -->
        @if($member->currentMembershipStatus())
        <div class="mb-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Current Status</h3>
                    <div class="space-y-2">
                        <p class="text-gray-700">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ 
                                match($member->currentMembershipStatus()->status) {
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-red-100 text-red-800',
                                    'new' => 'bg-blue-100 text-blue-800',
                                    'transferred' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800'
                                }
                            }}">
                                {{ $availableStatuses[$member->currentMembershipStatus()->status] }}
                            </span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Since: {{ $member->currentMembershipStatus()->start_date->format('M d, Y') }}
                        </p>
                        @if($member->currentMembershipStatus()->renewal_date)
                        <p class="text-sm {{ $member->currentMembershipStatus()->needsRenewal() ? 'text-red-600' : 'text-gray-600' }}">
                            Renewal Due: {{ $member->currentMembershipStatus()->renewal_date->format('M d, Y') }}
                        </p>
                        @endif
                    </div>
                </div>
                @if($member->currentMembershipStatus()->notes)
                <div class="bg-white p-4 rounded-lg border border-gray-200 max-w-md">
                    <h4 class="text-sm font-medium text-gray-700 mb-1">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $member->currentMembershipStatus()->notes }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Status History Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Changed By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($statuses as $status)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $status->start_date->format('M d, Y') }}
                            @if($status->end_date)
                                - {{ $status->end_date->format('M d, Y') }}
                                <span class="text-xs text-gray-400">({{ $status->getDurationInDays() }} days)</span>
                            @else
                                - Present
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $status->changedBy->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <div class="space-y-1">
                                @if($status->class_completed)
                                    <span class="inline-flex items-center text-xs text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Membership Class Completed
                                    </span>
                                @endif

                                @if($status->transfer_church)
                                    <div class="text-xs">
                                        Transferred to: {{ $status->transfer_church }}
                                        @if($status->transfer_date)
                                            <br>Transfer Date: {{ $status->transfer_date->format('M d, Y') }}
                                        @endif
                                    </div>
                                @endif

                                @if($status->renewal_date)
                                    <div class="text-xs {{ $status->needsRenewal() ? 'text-red-600' : '' }}">
                                        Renewal Due: {{ $status->renewal_date->format('M d, Y') }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <a href="{{ route('membership.status.show', [$member, $status]) }}" 
                               class="text-blue-600 hover:text-blue-900">View</a>
                            
                            @if($status->isCurrent())
                            <a href="{{ route('membership.status.edit', [$member, $status]) }}" 
                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            @else
                            <form action="{{ route('membership.status.destroy', [$member, $status]) }}" 
                                  method="POST" 
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this status record?')">
                                    Delete
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No status history found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $statuses->links() }}
        </div>
    </div>
</div>
@endsection