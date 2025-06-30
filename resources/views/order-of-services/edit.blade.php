@extends('layouts.admin')

@section('title', 'Edit Order of Service Item')
@section('header', 'Edit Order of Service Item')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Order of Service Item</h1>
                <p class="mt-1 text-sm text-gray-600">Editing "{{ $orderOfService->program }}" for {{ $service->name }}</p>
            </div>
            <a href="{{ route('services.order-of-services.index', $service->id) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to List
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 shadow-lg">
        <form action="{{ route('order-of-services.update', $orderOfService->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            @include('order-of-services._form', ['orderOfService' => $orderOfService])
            
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('services.order-of-services.index', $service->id) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    Update Item
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
