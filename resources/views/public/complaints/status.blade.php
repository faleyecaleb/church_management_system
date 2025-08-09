@extends('layouts.admin')

@section('title', 'Complaint Status')
@section('header', 'Complaint Status')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold">Reference: {{ $referenceNumber }}</h2>
        <p class="text-gray-600">Subject: {{ $complaint->subject }}</p>
        <p class="text-gray-600">Status: <span class="capitalize">{{ str_replace('_',' ', $complaint->status) }}</span></p>
        <p class="text-gray-600">Priority: <span class="capitalize">{{ $complaint->priority }}</span></p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-medium mb-2">Updates</h3>
        <ul class="space-y-3">
            @forelse($complaint->publicResponses as $response)
                <li class="text-sm">
                    <div class="text-gray-700">{{ $response->formatted_type }} • {{ $response->created_at->format('M j, Y g:i A') }}</div>
                    <div class="text-gray-800">{{ $response->message }}</div>
                </li>
            @empty
                <li class="text-sm text-gray-500">No updates yet.</li>
            @endforelse
        </ul>
    </div>

    @if($complaint->status === 'resolved')
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-medium mb-2">Was this resolved satisfactorily?</h3>
        <form action="{{ route('public.complaints.rating', $complaint) }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm text-gray-700">Rating (1-5)</label>
                <input type="number" min="1" max="5" name="rating" class="rounded-lg border-gray-300">
            </div>
            <div>
                <label class="block text-sm text-gray-700">Optional feedback</label>
                <textarea name="feedback" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
            </div>
            <div>
                <label class="block text-sm text-gray-700">Your email (to verify)</label>
                <input type="email" name="email" class="w-full rounded-lg border-gray-300">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white">Submit Feedback</button>
        </form>
    </div>
    @endif
</div>
@endsection
