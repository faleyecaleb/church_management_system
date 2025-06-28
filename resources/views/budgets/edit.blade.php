@extends('layouts.admin')

@section('title', 'Edit Budget')
@section('header', 'Edit Budget')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <form action="{{ route('budgets.update', $budget) }}" method="POST">
        @csrf
        @method('PUT')
        @include('budgets._form', ['budget' => $budget])
        <div class="mt-6">
            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Budget
            </button>
        </div>
    </form>
</div>
@endsection
