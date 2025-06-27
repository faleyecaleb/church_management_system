@extends('layouts.admin')

@section('title', 'Add Pledge')
@section('header', 'Add a New Pledge')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <form action="{{ route('pledges.store') }}" method="POST">
        @csrf
        @include('pledges._form')
        <div class="mt-6">
            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Pledge
            </button>
        </div>
    </form>
</div>
@endsection
