@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="text-center">
            <h2 class="text-2xl font-bold mb-4">{{ $service->name }} Check-in</h2>
            <p class="text-gray-600 mb-6">
                Scan this QR code with your mobile device to check in for the service.
                <br>
                <span class="text-sm">This QR code will expire in 15 minutes.</span>
            </p>

            <div class="flex justify-center mb-6">
                <div class="p-4 bg-white rounded-lg shadow-md inline-block">
                    {!! $qrCode !!}
                </div>
            </div>

            <div class="text-gray-600">
                <p class="mb-2"><strong>Service Time:</strong> {{ $service->start_time->format('g:i A') }} - {{ $service->end_time->format('g:i A') }}</p>
                <p class="mb-2"><strong>Location:</strong> {{ $service->location }}</p>
                @if($service->capacity)
                    <p class="mb-2"><strong>Capacity:</strong> {{ $service->capacity }} people</p>
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('attendance.service') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors">
                    Back to Attendance
                </a>
            </div>
        </div>
    </div>
</div>
@endsection