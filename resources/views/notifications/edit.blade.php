@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Notification</h2>
                <div class="space-x-4">
                    <a href="{{ route('notifications.show', $notification) }}" class="text-gray-600 hover:text-gray-900">View Details</a>
                    <a href="{{ route('notifications.index') }}" class="text-gray-600 hover:text-gray-900">Back to List</a>
                </div>
            </div>

            <form action="{{ route('notifications.update', $notification) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Current Status -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-500">Current Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ 
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

                        <span class="text-sm font-medium text-gray-500 ml-4">Type:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ ucfirst($notification->type) }}
                        </span>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" id="title" 
                           value="{{ old('title', $notification->title) }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Message -->
                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea name="message" id="message" rows="4" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                              required>{{ old('message', $notification->message) }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Schedule -->
                <div x-data="{ scheduled: {{ $notification->scheduled_at ? 'true' : 'false' }} }">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_scheduled" id="is_scheduled" 
                               x-model="scheduled"
                               class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="is_scheduled" class="ml-2 block text-sm text-gray-700">Schedule for later</label>
                    </div>

                    <div x-show="scheduled" class="mt-3">
                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                               value="{{ old('scheduled_at', $notification->scheduled_at ? $notification->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                               min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('scheduled_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Additional Data Fields -->
                <div class="space-y-4">
                    @if($notification->type === 'followup')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Follow-up Reason</label>
                            <input type="text" name="data[reason]" 
                                   value="{{ old('data.reason', $notification->data['reason'] ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                   placeholder="Reason for follow-up">
                        </div>
                    @endif

                    @if($notification->type === 'milestone')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Milestone Years</label>
                            <input type="number" name="data[milestone_years]" min="1"
                                   value="{{ old('data.milestone_years', $notification->data['milestone_years'] ?? '') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                   placeholder="Number of years">
                        </div>
                    @endif

                    @if($notification->type === 'custom')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Additional Notes</label>
                            <textarea name="data[notes]" rows="2"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                      placeholder="Any additional information">{{ old('data.notes', $notification->data['notes'] ?? '') }}</textarea>
                        </div>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('notifications.show', $notification) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Update Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection