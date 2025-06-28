@extends('layouts.admin')

@section('title', 'Budgets')
@section('header', 'Budgets')

@push('styles')
<style>
    .budget-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .budget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Budgets</h1>
            <p class="mt-1 text-sm text-gray-500">Manage and track all church budgets.</p>
        </div>
        <a href="{{ route('budgets.create') }}" class="mt-4 md:mt-0 inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-transform duration-300 hover:scale-105">
            <svg class="-ml-1 mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Budget
        </a>
    </div>

    <!-- Search and Filter -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow-sm">
        <form action="{{ route('budgets.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="search" name="name" id="search" value="{{ request('name') }}" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search by budget name...">
                    </div>
                </div>
                <div>
                    <select name="fiscal_year" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        <option value="">All Fiscal Years</option>
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" {{ request('fiscal_year') == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Budgets Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($budgets as $budget)
        <div class="budget-card bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $budget->name }}</h3>
                        <p class="text-sm font-medium text-indigo-600">{{ $budget->category }} - {{ $budget->department }}</p>
                    </div>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $budget->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $budget->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Allocated:</span>
                        <span class="font-semibold text-gray-800">${{ number_format($budget->amount, 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: {{ $budget->amount > 0 ? ($budget->used_amount / $budget->amount) * 100 : 0 }}%"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1 text-xs text-gray-500">
                        <span>Used: ${{ number_format($budget->used_amount, 2) }}</span>
                        <span>Remaining: ${{ number_format($budget->amount - $budget->used_amount, 2) }}</span>
                    </div>
                </div>
                <div class="mt-4 space-y-2 text-sm text-gray-600">
                    <p><strong>Fiscal Year:</strong> {{ $budget->fiscal_year }}</p>
                    <p><strong>Period:</strong> {{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-2">
                <a href="{{ route('budgets.show', $budget) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    View
                </a>
                <a href="{{ route('budgets.edit', $budget) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Edit
                </a>
                <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this budget?')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No budgets found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new budget.</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $budgets->links() }}
    </div>
</div>
@endsection
