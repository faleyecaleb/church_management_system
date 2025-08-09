@extends('layouts.admin')

@section('title', 'Complaint Submitted')
@section('header', 'Thank You')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
        <h2 class="text-xl font-semibold text-gray-900">Your complaint has been submitted</h2>
        <p class="text-gray-600 mt-2">Your reference number is:</p>
        <div class="text-2xl font-bold mt-1">{{ $referenceNumber }}</div>
        <p class="text-gray-600 mt-4">Keep this number to check the status of your complaint.</p>
        <div class="mt-6">
            <a href="{{ route('public.complaints.status') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Check Status</a>
        </div>
    </div>
</div>
@endsection
