@extends('layouts.admin')

@section('title', 'Edit Emergency Contact')
@section('header', 'Edit Emergency Contact')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <x-success-message />

    <form action="{{ route('members.emergency-contacts.update', [$member, $emergencyContact]) }}" method="POST" class="space-y-8 divide-y divide-gray-200">
        @csrf
        @method('PUT')
        <x-validation-errors />

        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Emergency Contact Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Please provide the details of your emergency contact.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="name" class="block text-sm font-medium text-gray-700">Full name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" value="{{ old('name', $emergencyContact->name) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="relationship" class="block text-sm font-medium text-gray-700">Relationship</label>
                        <div class="mt-1">
                            <input type="text" name="relationship" id="relationship" value="{{ old('relationship', $emergencyContact->relationship) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                        <div class="mt-1">
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $emergencyContact->phone) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="alternate_phone" class="block text-sm font-medium text-gray-700">Alternate phone</label>
                        <div class="mt-1">
                            <input type="tel" name="alternate_phone" id="alternate_phone" value="{{ old('alternate_phone', $emergencyContact->alternate_phone) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input type="email" name="email" id="email" value="{{ old('email', $emergencyContact->email) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                        <div class="mt-1">
                            <textarea name="address" id="address" rows="3"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('address', $emergencyContact->address) }}</textarea>
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_primary" id="is_primary" value="1"
                                       {{ old('is_primary', $emergencyContact->is_primary) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_primary" class="font-medium text-gray-700">Primary contact</label>
                                <p class="text-gray-500">Set this as the primary emergency contact.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5">
            <div class="flex justify-end">
                <a href="{{ route('members.show', $member) }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Cancel</a>
                <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Changes</button>
            </div>
        </div>
    </form>
</div>
@endsection