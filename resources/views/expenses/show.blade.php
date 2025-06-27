@extends('layouts.admin')

@section('title', 'Expense Details')
@section('header', 'Expense Details')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    <div class="bg-white shadow-lg rounded-xl overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $expense->description }}</h2>
                    <p class="text-sm font-medium text-indigo-600">{{ $expense->category }} - {{ $expense->department }}</p>
                </div>
                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                    @switch($expense->status)
                        @case('pending') bg-yellow-100 text-yellow-800 @break
                        @case('approved') bg-green-100 text-green-800 @break
                        @case('rejected') bg-red-100 text-red-800 @break
                    @endswitch
                ">
                    {{ ucfirst($expense->status) }}
                </span>
            </div>

            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div>
                        <p class="text-gray-500">Amount</p>
                        <p class="font-semibold text-gray-800">${{ number_format($expense->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Expense Date</p>
                        <p class="font-semibold text-gray-800">{{ $expense->expense_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Payment Method</p>
                        <p class="font-semibold text-gray-800">{{ $expense->payment_method }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Budget</p>
                        <p class="font-semibold text-gray-800">{{ $expense->budget->name }}</p>
                    </div>
                </div>
            </div>

            @if($expense->notes)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Notes</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $expense->notes }}</p>
            </div>
            @endif

            @if($expense->receipt)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Receipt</h3>
                <a href="{{ Storage::url($expense->receipt) }}" target="_blank" class="text-indigo-600 hover:text-indigo-800">View Receipt</a>
            </div>
            @endif
        </div>
        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-2">
            @if($expense->status === 'pending')
            <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="inline-block">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Approve
                </button>
            </form>
            <form action="{{ route('expenses.reject', $expense) }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="rejection_reason" value="Rejected by user."> {{-- Add a modal for this in a real app --}}
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Reject
                </button>
            </form>
            @endif
            <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                Edit
            </a>
            <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Back to Expenses
            </a>
        </div>
    </div>
</div>
@endsection
