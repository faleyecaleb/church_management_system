@extends('layouts.admin')

@section('header', 'Service QR Check-in')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto" id="printable-area">
        <div class="text-center">
            <h2 class="text-3xl font-bold mb-4 text-gray-900">{{ $service->name }} Check-in</h2>
            <p class="text-gray-600 mb-6 text-lg">
                Scan this QR code with your mobile device camera to check in for the service.
                <br>
                <span class="text-sm font-semibold text-red-600 mt-2 block">
                    Note: This QR code is valid until the service ends at {{ $expiryTime->format('g:i A') }}.
                    <br>
                    You must be physically present at the church to check in.
                </span>
            </p>

            <div class="flex justify-center mb-8">
                <div class="p-6 bg-white rounded-xl shadow-xl border-2 border-gray-100 inline-block">
                    {!! $qrCode !!}
                </div>
            </div>

            <div class="text-gray-700 bg-gray-50 rounded-lg p-4 mb-6 text-left max-w-md mx-auto">
                <p class="mb-2"><strong>Service Time:</strong> {{ $service->start_time->format('g:i A') }} - {{ $service->end_time ? $service->end_time->format('g:i A') : 'TBD' }}</p>
                <p class="mb-2"><strong>Location:</strong> {{ $service->location ?? 'Main Auditorium' }}</p>
            </div>

            <div class="mt-8 flex justify-center space-x-4 no-print">
                <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print QR Code
                </button>
                <a href="{{ route('attendance.service') }}" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition-colors shadow-md">
                    Back to Attendance
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #printable-area, #printable-area * {
            visibility: visible;
        }
        #printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            box-shadow: none !important;
            border: none !important;
            padding: 2rem !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>
@endsection