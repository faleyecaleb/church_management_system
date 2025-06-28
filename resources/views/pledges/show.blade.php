@extends('layouts.admin')

@section('title', 'Pledge Details')
@section('header', 'Pledge Details')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $pledge->campaign_name }}</h2>
                    <p class="text-sm font-medium text-indigo-600">{{ $pledge->member->full_name }}</p>
                </div>
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                    @switch($pledge->status)
                        @case('active') bg-blue-100 text-blue-800 @break
                        @case('completed') bg-green-100 text-green-800 @break
                        @case('defaulted') bg-red-100 text-red-800 @break
                    @endswitch
                ">
                    {{ ucfirst(str_replace('_', ' ', $pledge->status)) }}
                </span>
            </div>

            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500">Pledge Amount</p>
                        <p class="font-semibold text-gray-800">₦{{ number_format($pledge->total_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Paid Amount</p>
                        <p class="font-semibold text-gray-800">₦{{ number_format($pledge->amount_paid, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Start Date</p>
                        <p class="font-semibold text-gray-800">{{ $pledge->start_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">End Date</p>
                        <p class="font-semibold text-gray-800">{{ $pledge->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Payment Progress</h3>
                <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
                    <div class="bg-green-500 h-4 rounded-full text-xs font-medium text-white text-center p-0.5 leading-none" 
                         style="width: {{ $pledge->total_amount > 0 ? ($pledge->amount_paid / $pledge->total_amount) * 100 : 0 }}%">
                         {{ round($pledge->total_amount > 0 ? ($pledge->amount_paid / $pledge->total_amount) * 100 : 0) }}%
                    </div>
                </div>
            </div>

            @if($pledge->notes)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Notes</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $pledge->notes }}</p>
            </div>
            @endif
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-2">
            <a href="{{ route('pledges.edit', $pledge) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Edit
            </a>
            <a href="{{ route('pledges.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Pledges
            </a>
        </div>
    </div>
</div>
@endsection