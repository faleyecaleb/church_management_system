@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $notification->title }}</h2>
                    <div class="flex items-center space-x-4">
                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Mark as Read
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('notifications.index') }}" class="text-gray-600 hover:text-gray-900">
                            Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-4">
                <!-- Status and Type Badges -->
                <div class="flex items-center space-x-4 mb-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium {{ 
                        match($notification->status) {
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'scheduled' => 'bg-blue-100 text-blue-800',
                            'sent' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        }
                    }}">
                        {{ ucfirst($notification->status) }}
                    </span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        {{ ucfirst($notification->type) }}
                    </span>

                    @if($notification->read_at)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                            Read {{ $notification->read_at->diffForHumans() }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Unread
                        </span>
                    @endif
                </div>

                <!-- Message -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Message</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $notification->message }}</p>
                </div>

                <!-- Recipient Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Recipient</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Name</p>
                                <p class="mt-1">{{ $notification->recipient->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Type</p>
                                <p class="mt-1">{{ class_basename($notification->recipient_type) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Timing Information -->
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Timing</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Created</p>
                                <p class="mt-1">{{ $notification->created_at->format('M d, Y H:i:s') }}</p>
                            </div>

                            @if($notification->scheduled_at)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Scheduled For</p>
                                <p class="mt-1">{{ $notification->scheduled_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            @endif

                            @if($notification->sent_at)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Sent At</p>
                                <p class="mt-1">{{ $notification->sent_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            @endif

                            @if($notification->read_at)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Read At</p>
                                <p class="mt-1">{{ $notification->read_at->format('M d, Y H:i:s') }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Additional Data -->
                @if($notification->data)
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Additional Information</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dl class="grid grid-cols-2 gap-4">
                            @foreach($notification->data as $key => $value)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 mt-6">
                    @if(!$notification->sent_at)
                        @can('edit-notifications')
                        <a href="{{ route('notifications.edit', $notification) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Edit Notification
                        </a>
                        @endcan

                        @can('delete-notifications')
                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this notification?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Delete Notification
                            </button>
                        </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection