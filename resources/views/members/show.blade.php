@extends('layouts.admin')

@section('title', $member->full_name)
@section('header', 'Member Profile')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <x-success-message />

    <!-- Profile Header -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
            <div class="flex items-center">
                <img class="h-16 w-16 rounded-full" 
                     src="{{ $member->profile_photo ? Storage::url($member->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($member->full_name) }}" 
                     alt="{{ $member->full_name }}">
                <div class="ml-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ $member->full_name }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Member since {{ $member->created_at->format('M d, Y') }}
                    </p>
                </div>
            </div>
            @can('member.update')
            <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Profile
            </a>
            @endcan
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $member->email }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $member->phone ?? 'Not provided' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $member->date_of_birth ? $member->date_of_birth->format('M d, Y') : 'Not provided' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Baptism Date</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $member->baptism_date ? $member->baptism_date->format('M d, Y') : 'Not provided' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">Address</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $member->address ?? 'Not provided' }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($member->department)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800">
                                {{ $member->department }}
                            </span>
                        @else
                            Not assigned
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm font-medium text-gray-500">Membership Status</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->membership_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($member->membership_status) }}
                        </span>
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    <!-- Emergency Contacts -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Emergency Contacts</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Contact information for emergencies.</p>
            </div>
            @can('member.update')
            <button type="button" onclick="document.getElementById('emergency-contacts-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Contact
            </button>
            @endcan
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @forelse($member->emergencyContacts as $contact)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $contact->name }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ $contact->relationship }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <div class="flex space-x-4">
                                <a href="tel:{{ $contact->phone }}" class="text-indigo-600 hover:text-indigo-900">{{ $contact->phone }}</a>
                                @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-indigo-600 hover:text-indigo-900">{{ $contact->email }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                @empty
                <li class="px-4 py-4 sm:px-6 text-center text-gray-500">No emergency contacts added yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <div>
                <h3 class="text-lg leading-6 font-medium text-gray-900">Documents</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Member's important documents and certificates.</p>
            </div>
            @can('member.update')
            <button type="button" onclick="document.getElementById('document-upload-modal').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
                Upload Document
            </button>
            @endcan
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @forelse($member->documents as $document)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $document->title }}</h4>
                            <p class="mt-1 text-sm text-gray-500">{{ $document->document_type }}</p>
                            @if($document->description)
                            <p class="mt-1 text-sm text-gray-500">{{ $document->description }}</p>
                            @endif
                        </div>
                        <div class="ml-4 flex-shrink-0 flex space-x-4">
                            <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-500">View</a>
                            @can('member.update')
                            <form action="{{ route('members.documents.delete', [$member, $document]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-600 hover:text-red-500" onclick="return confirm('Are you sure you want to delete this document?')">Delete</button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </li>
                @empty
                <li class="px-4 py-4 sm:px-6 text-center text-gray-500">No documents uploaded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection