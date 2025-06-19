@extends('errors.layout')

@section('title', 'Service Unavailable')
@section('code', '503')
@section('message', 'Sorry, we are doing some maintenance. Please check back soon.')

@section('content')
<div class="mt-4">
    <div class="animate-pulse flex space-x-4 justify-center">
        <div class="h-3 w-3 bg-indigo-400 rounded-full"></div>
        <div class="h-3 w-3 bg-indigo-400 rounded-full"></div>
        <div class="h-3 w-3 bg-indigo-400 rounded-full"></div>
    </div>
    @if(isset($exception) && $exception->wentDownAt)
        <p class="mt-4 text-sm text-gray-500">
            Estimated downtime: {{ $exception->wentDownAt->addMinutes($exception->retryAfter ?? 0)->diffForHumans() }}
        </p>
    @endif
</div>
@endsection