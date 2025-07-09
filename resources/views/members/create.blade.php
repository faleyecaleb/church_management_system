@extends('layouts.admin')

@section('title', 'Add Member')
@section('header', 'Add New Member')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <h2 class="text-2xl font-bold text-white">Create New Member Profile</h2>
            <p class="mt-1 text-indigo-100">Add a new member to the church database</p>
        </div>

        <div class="p-6">
            <x-success-message />

            <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <x-validation-errors />

                <div class="space-y-8">
                    <!-- Profile Photo -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Profile Photo</h3>
                                <p class="mt-1 text-sm text-gray-500">Upload a profile photo for the member.</p>
                            </div>
                            <div class="flex items-center space-x-6">
                                <div class="h-32 w-32 rounded-full ring-4 ring-indigo-50 overflow-hidden bg-gray-100">
                                    <div class="h-32 w-32 rounded-full bg-gray-200 flex items-center justify-center">
                                        <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <label class="block">
                                        <span class="sr-only">Choose profile photo</span>
                                        <input type="file" name="profile_photo" id="profile_photo" accept="image/*"
                                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6
                                                      file:rounded-full file:border-0 file:text-sm file:font-semibold
                                                      file:bg-gradient-to-r file:from-indigo-600 file:to-purple-600 file:text-white
                                                      hover:file:bg-gradient-to-r hover:file:from-indigo-700 hover:file:to-purple-700
                                                      focus:outline-none">
                                    </label>
                                    <p class="mt-2 text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Personal Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Basic personal details and contact information.</p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First name *</label>
                                <div class="mt-1">
                                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last name *</label>
                                <div class="mt-1">
                                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address *</label>
                                <div class="mt-1">
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone number</label>
                                <div class="mt-1">
                                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of birth</label>
                                <div class="mt-1">
                                    <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                <div class="mt-1">
                                    <textarea name="address" id="address" rows="3"
                                              class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Church Information -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Church Information</h3>
                                <p class="mt-1 text-sm text-gray-500">Information about church membership and baptism.</p>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="baptism_date" class="block text-sm font-medium text-gray-700">Baptism date</label>
                                <div class="mt-1">
                                    <input type="date" name="baptism_date" id="baptism_date" value="{{ old('baptism_date') }}"
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="department" class="block text-sm font-medium text-gray-700">Department *</label>
                                <div class="mt-1">
                                    <input type="text" name="department" id="department" value="{{ old('department') }}" required
                                           placeholder="e.g., Youth, Choir, Ushers"
                                           class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="membership_status" class="block text-sm font-medium text-gray-700">Membership status *</label>
                                <div class="mt-1">
                                    <select name="membership_status" id="membership_status" required
                                            class="shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-lg px-4 py-3 bg-white transition duration-150 ease-in-out">
                                        <option value="">Select status</option>
                                        <option value="active" {{ old('membership_status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('membership_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="transferred" {{ old('membership_status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                                        <option value="deceased" {{ old('membership_status') === 'deceased' ? 'selected' : '' }}>Deceased</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles Section (if roles exist) -->
                    @if(isset($roles) && $roles->count() > 0)
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Church Roles</h3>
                                <p class="mt-1 text-sm text-gray-500">Assign roles and responsibilities to the member.</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <fieldset>
                                <legend class="sr-only">Church Roles</legend>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach($roles as $role)
                                    <div class="relative flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="role_{{ $role->id }}" name="roles[]" type="checkbox" value="{{ $role->id }}"
                                                   {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="role_{{ $role->id }}" class="font-medium text-gray-700">{{ $role->name }}</label>
                                            @if($role->description)
                                            <p class="text-gray-500">{{ $role->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </fieldset>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="pt-6 border-t border-gray-200">
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('members.index') }}" 
                           class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-lg text-base font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Member
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection