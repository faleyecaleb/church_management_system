@extends('layouts.admin')

@section('title', 'Add Member')
@section('header', 'Add New Member')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white/80 backdrop-blur-xl rounded-3xl border border-white/30 shadow-xl p-8 hover:bg-white/90 transition-all duration-300">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Member Profile</h2>
            <p class="text-gray-600">Fill in the member's information to create their profile in the church database.</p>
        </div>
        
        <form action="{{ route('members.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Basic Information -->
                <div class="bg-gray-50/50 backdrop-blur-sm rounded-2xl p-6 space-y-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center space-x-3 mb-2">
                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                    </div>
                    
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="bg-gray-50/50 backdrop-blur-sm rounded-2xl p-6 space-y-6 border border-gray-100 shadow-sm">
                    <div class="flex items-center space-x-3 mb-2">
                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900">Additional Information</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="relative group">
                            <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                            <div class="relative">
                                <input type="date" name="date_of_birth" id="date_of_birth" value="{{ old('date_of_birth') }}"
                                       class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <label for="baptism_date" class="block text-sm font-medium text-gray-700 mb-1">Baptism Date</label>
                            <div class="relative">
                                <input type="date" name="baptism_date" id="baptism_date" value="{{ old('baptism_date') }}"
                                       class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <div class="relative">
                                <select name="department" id="department" required
                                        class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300 appearance-none">
                                    <option value="">Select Department</option>
                                    <option value="Media" {{ old('department') === 'Media' ? 'selected' : '' }}>Media</option>
                                    <option value="Choir" {{ old('department') === 'Choir' ? 'selected' : '' }}>Choir</option>
                                    <option value="Ushers" {{ old('department') === 'Ushers' ? 'selected' : '' }}>Ushers</option>
                                    <option value="Dance" {{ old('department') === 'Dance' ? 'selected' : '' }}>Dance</option>
                                    <option value="Prayer" {{ old('department') === 'Prayer' ? 'selected' : '' }}>Prayer</option>
                                    <option value="Lost but Found" {{ old('department') === 'Lost but Found' ? 'selected' : '' }}>Lost but Found</option>
                                    <option value="Drama" {{ old('department') === 'Drama' ? 'selected' : '' }}>Drama</option>
                                    <option value="Sanctuary" {{ old('department') === 'Sanctuary' ? 'selected' : '' }}>Sanctuary</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <label for="membership_status" class="block text-sm font-medium text-gray-700 mb-1">Membership Status</label>
                            <div class="relative">
                                <select name="membership_status" id="membership_status" required
                                        class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300 appearance-none">
                                    <option value="active" {{ old('membership_status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('membership_status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    <option value="transferred" {{ old('membership_status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                                    <option value="deceased" {{ old('membership_status') === 'deceased' ? 'selected' : '' }}>Deceased</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                </div>
                            </div>
                        </div>

                        {{-- <div class="relative group">
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                            <div class="relative">
                                <select name="department" id="department"
                                        class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300 appearance-none">
                                    <option value="">Select Department</option>
                                    <option value="Media" {{ old('department') === 'Media' ? 'selected' : '' }}>Media</option>
                                    <option value="Choir" {{ old('department') === 'Choir' ? 'selected' : '' }}>Choir</option>
                                    <option value="Ushers" {{ old('department') === 'Ushers' ? 'selected' : '' }}>Ushers</option>
                                    <option value="Dance" {{ old('department') === 'Dance' ? 'selected' : '' }}>Dance</option>
                                    <option value="Prayer" {{ old('department') === 'Prayer' ? 'selected' : '' }}>Prayer</option>
                                    <option value="Lost but Found" {{ old('department') === 'Lost but Found' ? 'selected' : '' }}>Lost but Found</option>
                                    <option value="Drama" {{ old('department') === 'Drama' ? 'selected' : '' }}>Drama</option>
                                    <option value="Sanctuary" {{ old('department') === 'Sanctuary' ? 'selected' : '' }}>Sanctuary</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div> --}}

                        <div class="relative group">
                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                            <div class="relative">
                                <input type="file" name="profile_photo" id="profile_photo" accept="image/*"
                                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 transition duration-200 ease-in-out">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50/50 backdrop-blur-sm rounded-2xl p-6 space-y-4 border border-gray-100 shadow-sm">
                <div class="flex items-center space-x-3 mb-2">
                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">Address Information</h3>
                </div>
                
                <div class="relative group">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
                    <div class="relative">
                        <textarea name="address" id="address" rows="3" 
                            class="block w-full pl-11 pr-4 py-3 rounded-xl border-gray-200 bg-white/50 shadow-sm transition duration-200 ease-in-out focus:bg-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 group-hover:border-gray-300"
                            placeholder="Enter complete address...">{{ old('address') }}</textarea>
                        <div class="absolute top-3 left-3">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6">
                <a href="{{ route('members.index') }}" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-200 ease-in-out">
                    <svg class="-ml-1 mr-3 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Cancel
                </a>
                <button type="submit" 
                    class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-base font-medium rounded-xl text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition duration-200 ease-in-out">
                    <svg class="-ml-1 mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Create Member
                </button>
            </div>
        </form>
    </div>
</div>
@endsection