@extends('layouts.admin')

@section('title', 'Check Complaint Status')
@section('header', 'Check Complaint Status')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('public.complaints.check-status') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                <input type="text" name="reference_number" class="w-full rounded-lg border-gray-300" placeholder="e.g. CMP-000123">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Your Email</label>
                <input type="email" name="email" class="w-full rounded-lg border-gray-300" placeholder="name@example.com">
            </div>
            @if ($errors->any())
                <p class="text-sm text-red-600">{{ $errors->first() }}</p>
            @endif
            <div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Check Status</button>
            </div>
        </form>
    </div>
</div>
@endsection
