@extends('layouts.admin')

@section('title', 'Budget Details')
@section('header', 'Budget Details')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $budget->name }}</h2>
                    <p class="text-sm font-medium text-indigo-600">{{ $budget->category }} - {{ $budget->department }}</p>
                </div>
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $budget->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $budget->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500">Allocated Amount</p>
                        <p class="font-semibold text-gray-800">${{ number_format($budget->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Used Amount</p>
                        <p class="font-semibold text-gray-800">${{ number_format($budget->used_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Fiscal Year</p>
                        <p class="font-semibold text-gray-800">{{ $budget->fiscal_year }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Period</p>
                        <p class="font-semibold text-gray-800">{{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Budget Utilization</h3>
                <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
                    <div class="bg-blue-500 h-4 rounded-full text-xs font-medium text-white text-center p-0.5 leading-none" 
                         style="width: {{ $budget->amount > 0 ? ($budget->used_amount / $budget->amount) * 100 : 0 }}%">
                         {{ round($budget->amount > 0 ? ($budget->used_amount / $budget->amount) * 100 : 0) }}%
                    </div>
                </div>
            </div>

            @if($budget->description)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Description</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $budget->description }}</p>
            </div>
            @endif
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-2">
            <a href="{{ route('budgets.edit', $budget) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Edit
            </a>
            <a href="{{ route('budgets.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Budgets
            </a>
        </div>
    </div>
</div>
@endsection
