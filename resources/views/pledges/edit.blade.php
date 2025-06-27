@extends('layouts.admin')

@section('title', 'Edit Pledge')
@section('header', 'Edit Pledge')

@section('content')
<div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-md">
    <form action="{{ route('pledges.update', $pledge) }}" method="POST">
        @csrf
        @method('PUT')
        @include('pledges._form', ['pledge' => $pledge])
        <div class="mt-6">
            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Update Pledge
            </button>
        </div>
    </form>
</div>
@endsection
