@extends('layouts.admin')

@section('title', 'Add Service')
@section('header', 'Add a New Service')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <form action="{{ route('services.store') }}" method="POST">
        @csrf
        @include('services._form')
        <div class="mt-6">
            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Service
            </button>
        </div>
    </form>
</div>
@endsection
