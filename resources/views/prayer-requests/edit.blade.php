@extends('layouts.admin')

@section('title', 'Edit Prayer Request')
@section('header', 'Edit Prayer Request')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Prayer Request</h1>
                <p class="mt-1 text-sm text-gray-600">Update your prayer request details</p>
            </div>
            <a href="{{ route('prayer-requests.show', $prayerRequest) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Request
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <form action="{{ route('prayer-requests.update', $prayerRequest) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Prayer Request Title *</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title', $prayerRequest->title) }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Brief title for your prayer request">
                </div>

                <!-- Privacy Setting -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Privacy Setting</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="is_public" 
                                   value="1" 
                                   {{ old('is_public', !($prayerRequest->is_private ?? false) ? '1' : '0') === '1' ? 'checked' : '' }}
                                   class="form-radio text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <strong>Public</strong> - Visible to all members
                            </span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="is_public" 
                                   value="0" 
                                   {{ old('is_public', ($prayerRequest->is_private ?? false) ? '0' : '1') === '0' ? 'checked' : '' }}
                                   class="form-radio text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">
                                <strong>Private</strong> - Only visible to you and admins
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Prayer Target -->
                <div>
                    <label for="prayer_target" class="block text-sm font-medium text-gray-700 mb-2">Prayer Target (Optional)</label>
                    <input type="number" 
                           name="prayer_target" 
                           id="prayer_target" 
                           value="{{ old('prayer_target', $prayerRequest->prayer_target ?? '') }}"
                           min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="How many prayers are you hoping for?">
                    <p class="mt-1 text-xs text-gray-500">Set a goal for the number of prayers you'd like to receive</p>
                </div>

                <!-- Prayer Frequency -->
                <div>
                    <label for="prayer_frequency" class="block text-sm font-medium text-gray-700 mb-2">Prayer Frequency (Optional)</label>
                    <select name="prayer_frequency" 
                            id="prayer_frequency"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">No specific frequency</option>
                        <option value="1" {{ old('prayer_frequency', $prayerRequest->prayer_frequency ?? '') == '1' ? 'selected' : '' }}>Daily</option>
                        <option value="3" {{ old('prayer_frequency', $prayerRequest->prayer_frequency ?? '') == '3' ? 'selected' : '' }}>Every 3 days</option>
                        <option value="7" {{ old('prayer_frequency', $prayerRequest->prayer_frequency ?? '') == '7' ? 'selected' : '' }}>Weekly</option>
                        <option value="14" {{ old('prayer_frequency', $prayerRequest->prayer_frequency ?? '') == '14' ? 'selected' : '' }}>Bi-weekly</option>
                        <option value="30" {{ old('prayer_frequency', $prayerRequest->prayer_frequency ?? '') == '30' ? 'selected' : '' }}>Monthly</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">How often would you like people to pray for this request?</p>
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date (Optional)</label>
                    <input type="date" 
                           name="end_date" 
                           id="end_date" 
                           value="{{ old('end_date', $prayerRequest->end_date ? $prayerRequest->end_date->format('Y-m-d') : '') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">When should this prayer request automatically be archived?</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Prayer Request Details *</label>
                <textarea name="description" 
                          id="description" 
                          rows="6"
                          required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Share the details of your prayer request. Be as specific as you feel comfortable being...">{{ old('description', $prayerRequest->description) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Provide details about what you'd like prayer for. The more specific you are, the more focused the prayers can be.</p>
            </div>

            <!-- Current Status Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-800 mb-2">Current Status</h4>
                <div class="text-sm text-blue-700 space-y-1">
                    <p>• Status: <strong>{{ ucfirst($prayerRequest->status) }}</strong></p>
                    <p>• Total Prayers: <strong>{{ $prayerRequest->prayer_count ?? 0 }}</strong></p>
                    <p>• Created: <strong>{{ $prayerRequest->created_at->format('F j, Y') }}</strong></p>
                    @if($prayerRequest->last_prayed_at)
                        <p>• Last Prayed: <strong>{{ $prayerRequest->last_prayed_at->diffForHumans() }}</strong></p>
                    @endif
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('prayer-requests.show', $prayerRequest) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Update Prayer Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection