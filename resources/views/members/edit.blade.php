@extends('layouts.admin')

@section('title', 'Edit Member')
@section('header', 'Edit Member')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <h2 class="text-2xl font-bold text-white">Edit Member Profile</h2>
            <p class="mt-1 text-indigo-100">Update information for {{ $member->first_name }} {{ $member->last_name }}</p>
        </div>

        <div class="p-6">
            <x-success-message />

            <form action="{{ route('members.update', $member) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                <x-validation-errors />

                <div class="space-y-8">
                    @if(!auth()->user()->church || auth()->user()->church->type !== 'youth')
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
                    @endif

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
                                <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $member->first_name) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last name (Surname) *</label>
                                <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $member->last_name) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="other_names" class="block text-sm font-medium text-gray-700">Others</label>
                                <input type="text" name="other_names" id="other_names" value="{{ old('other_names', $member->other_names) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email address *</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $member->email) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone number (Primary) *</label>
                                <input type="tel" name="phone" id="phone" value="{{ old('phone', $member->phone) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="gender" class="block text-sm font-medium text-gray-700">Gender *</label>
                                <select name="gender" id="gender" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $member->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $member->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="birth_month" class="block text-sm font-medium text-gray-700">Month of Birth *</label>
                                <select name="birth_month" id="birth_month" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Month</option>
                                    @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                        <option value="{{ $month }}" {{ old('birth_month', $member->birth_month) == $month ? 'selected' : '' }}>{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="birth_day" class="block text-sm font-medium text-gray-700">Day of Birth *</label>
                                <select name="birth_day" id="birth_day" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Day</option>
                                    @for($i = 1; $i <= 31; $i++)
                                        <option value="{{ $i }}" {{ old('birth_day', $member->birth_day) == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status *</label>
                                <select name="marital_status" id="marital_status" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Status</option>
                                    <option value="SINGLE" {{ old('marital_status', $member->marital_status) == 'SINGLE' ? 'selected' : '' }}>SINGLE</option>
                                    <option value="MARRIED" {{ old('marital_status', $member->marital_status) == 'MARRIED' ? 'selected' : '' }}>MARRIED</option>
                                    <option value="ENGAGED" {{ old('marital_status', $member->marital_status) == 'ENGAGED' ? 'selected' : '' }}>ENGAGED</option>
                                    <option value="WIDOWED" {{ old('marital_status', $member->marital_status) == 'WIDOWED' ? 'selected' : '' }}>WIDOWED</option>
                                </select>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="partner_name" class="block text-sm font-medium text-gray-700">Name of Partner (If married)</label>
                                <input type="text" name="partner_name" id="partner_name" value="{{ old('partner_name', $member->partner_name) }}"
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-6">
                                <label for="emergency_contact_details" class="block text-sm font-medium text-gray-700">Emergency Contact Name & Phone Number</label>
                                <input type="text" name="emergency_contact_details" id="emergency_contact_details" value="{{ old('emergency_contact_details', $member->emergency_contact_details) }}"
                                          class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <!-- Location & Origin -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Location & Origin</h3>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            <div class="sm:col-span-3">
                                <label for="state_of_origin" class="block text-sm font-medium text-gray-700">State of Origin *</label>
                                <input type="text" name="state_of_origin" id="state_of_origin" value="{{ old('state_of_origin', $member->state_of_origin) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="lga_of_origin" class="block text-sm font-medium text-gray-700">Local Government of Origin *</label>
                                <input type="text" name="lga_of_origin" id="lga_of_origin" value="{{ old('lga_of_origin', $member->lga_of_origin) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="state_of_residence" class="block text-sm font-medium text-gray-700">State of Residence *</label>
                                <input type="text" name="state_of_residence" id="state_of_residence" value="{{ old('state_of_residence', $member->state_of_residence) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-3">
                                <label for="city_of_residence" class="block text-sm font-medium text-gray-700">City of Residence *</label>
                                <input type="text" name="city_of_residence" id="city_of_residence" value="{{ old('city_of_residence', $member->city_of_residence) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="sm:col-span-6">
                                <label for="address" class="block text-sm font-medium text-gray-700">Street No and Name (eg: 2, Korogboji) *</label>
                                <input type="text" name="address" id="address" value="{{ old('address', $member->address) }}" required
                                       class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <!-- Professional & Church Information -->
                    <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Professional & Church Information</h3>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                            @if(auth()->user()->church && auth()->user()->church->type === 'youth')
                                <div class="sm:col-span-3">
                                    <label for="profession" class="block text-sm font-medium text-gray-700">Vocation / Occupation *</label>
                                    <select name="profession" id="profession" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Vocation</option>
                                        <option value="Student" {{ old('profession', $member->profession) == 'Student' ? 'selected' : '' }}>Student</option>
                                        <option value="Working Student" {{ old('profession', $member->profession) == 'Working Student' ? 'selected' : '' }}>Working Student</option>
                                        <option value="Working Class" {{ old('profession', $member->profession) == 'Working Class' ? 'selected' : '' }}>Working Class</option>
                                    </select>
                                </div>
                            @else
                                <div class="sm:col-span-3">
                                    <label for="profession" class="block text-sm font-medium text-gray-700">Profession / Occupation *</label>
                                    <input type="text" name="profession" id="profession" value="{{ old('profession', $member->profession) }}" required
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            @endif

                            <div class="sm:col-span-3">
                                <label for="church_group" class="block text-sm font-medium text-gray-700">Group in Church</label>
                                <select name="church_group" id="church_group"
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Group</option>
                                    @foreach(['The Levites', 'The Light bearers', 'The Root of Jesse', 'Ark of Covenant', 'God\'s Workmanship', 'Glorious star', 'Bread of Life', 'Wisdom of God', 'The Gospellers', 'Balm of Gilead', 'New creature', 'Heaven Ambassadors', 'Battle axe', 'PEACE FELLOWSHIP', 'REDEEMED', 'Light of the World', 'THE LORD CHOSEN', 'Salt of the World', 'Daughters of Zion'] as $group)
                                        <option value="{{ $group }}" {{ old('church_group', $member->church_group) == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="sm:col-span-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Departments in Church * <span class="text-xs font-normal text-gray-500">(Select all that apply)</span></label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-xl bg-gray-50/50">
                                    @if(auth()->user()->church && auth()->user()->church->type === 'youth')
                                        @foreach(['CHOIR', 'EVANGELISM', 'AESTHETICS', 'SUNDAY SCHOOL', 'DOCUMENTATION', 'DRAMA', 'MEDIA', 'PROTOCOL', 'PRAYER', 'NONE'] as $dept)
                                            <label class="inline-flex items-center space-x-3 p-2 bg-white rounded-lg border border-gray-100 hover:border-indigo-500 hover:shadow-sm transition-all duration-200 cursor-pointer">
                                                <input type="checkbox" name="departments[]" value="{{ $dept }}" 
                                                       {{ is_array(old('departments', $member->department_names)) && in_array($dept, old('departments', $member->department_names)) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition-colors duration-200">
                                                <span class="text-sm font-medium text-gray-700">{{ $dept }}</span>
                                            </label>
                                        @endforeach
                                    @else
                                        @foreach(['CHOIR', 'EVANGELISM', 'USHERING', 'DECORATION', 'INTERPRETATION', 'SUNDAY SCHOOL', 'DOCUMENTATION', 'DRAMA', 'SECURITY', 'MEDIA', 'PROTOCOL', 'SANCTUARY KEEPER', 'TECHNICAL', 'PRAYER', 'NONE'] as $dept)
                                            <label class="inline-flex items-center space-x-3 p-2 bg-white rounded-lg border border-gray-100 hover:border-indigo-500 hover:shadow-sm transition-all duration-200 cursor-pointer">
                                                <input type="checkbox" name="departments[]" value="{{ $dept }}" 
                                                       {{ is_array(old('departments', $member->department_names)) && in_array($dept, old('departments', $member->department_names)) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded transition-colors duration-200">
                                                <span class="text-sm font-medium text-gray-700">{{ $dept }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            @if(!auth()->user()->church || auth()->user()->church->type !== 'youth')
                                <div class="sm:col-span-2">
                                    <label for="is_baptized" class="block text-sm font-medium text-gray-700">Are you Baptized? *</label>
                                    <select name="is_baptized" id="is_baptized" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Option</option>
                                        <option value="YES" {{ old('is_baptized', $member->is_baptized) == 'YES' ? 'selected' : '' }}>YES</option>
                                        <option value="NO" {{ old('is_baptized', $member->is_baptized) == 'NO' ? 'selected' : '' }}>NO</option>
                                        <option value="MAYBE" {{ old('is_baptized', $member->is_baptized) == 'MAYBE' ? 'selected' : '' }}>MAYBE</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="baptism_year_and_place" class="block text-sm font-medium text-gray-700">What Year and Where? (If baptized)</label>
                                    <input type="text" name="baptism_year_and_place" id="baptism_year_and_place" value="{{ old('baptism_year_and_place', $member->baptism_year_and_place) }}"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="baptism_church_name" class="block text-sm font-medium text-gray-700">Name of the Church</label>
                                    <input type="text" name="baptism_church_name" id="baptism_church_name" value="{{ old('baptism_church_name', $member->baptism_church_name) }}"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            @endif

                            @if(auth()->user()->church && auth()->user()->church->type === 'youth')
                                <!-- Youth Specific Baptism Fields -->
                                <div class="sm:col-span-3">
                                    <label for="is_baptized" class="block text-sm font-medium text-gray-700">Are you baptized? *</label>
                                    <select name="is_baptized" id="is_baptized" required
                                            class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            onchange="document.getElementById('baptism_details_div').classList.toggle('hidden', this.value !== '1' && this.value !== 'YES')">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ old('is_baptized', $member->is_baptized) == '1' || old('is_baptized', $member->is_baptized) == 'YES' ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ old('is_baptized', $member->is_baptized) == '0' || old('is_baptized', $member->is_baptized) == 'NO' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                                
                                <div id="baptism_details_div" class="sm:col-span-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6 {{ (old('is_baptized', $member->is_baptized) == '1' || old('is_baptized', $member->is_baptized) == 'YES') ? '' : 'hidden' }} p-4 bg-indigo-50 rounded-lg">
                                    <div class="sm:col-span-3">
                                        <label for="baptism_year_and_place" class="block text-sm font-medium text-gray-700">Year and Place of Baptism</label>
                                        <input type="text" name="baptism_year_and_place" id="baptism_year_and_place" value="{{ old('baptism_year_and_place', $member->baptism_year_and_place) }}" placeholder="e.g., 2018 at Lagos"
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <label for="baptism_church_name" class="block text-sm font-medium text-gray-700">Church Name (If not CAC Hosanna)</label>
                                        <input type="text" name="baptism_church_name" id="baptism_church_name" value="{{ old('baptism_church_name', $member->baptism_church_name) }}"
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                    </div>
                                </div>
                            @endif

                            <div class="sm:col-span-6">
                                <label for="spiritual_gifts" class="block text-sm font-medium text-gray-700">Spiritual Gifts</label>
                                <textarea name="spiritual_gifts" id="spiritual_gifts" rows="3"
                                          class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                          placeholder="e.g., Teaching, Prophecy, Giving, etc.">{{ old('spiritual_gifts', $member->spiritual_gifts) }}</textarea>
                            </div>
                        </div>
                    </div>

                    @if(!auth()->user()->church || auth()->user()->church->type !== 'youth')
                        <!-- System Information -->
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">System Information</h3>
                                    <p class="mt-1 text-sm text-gray-500">Roles and account status.</p>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="membership_status" class="block text-sm font-medium text-gray-700">Membership status</label>
                                    <select id="membership_status" name="membership_status"
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="active" {{ old('membership_status', $member->membership_status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('membership_status', $member->membership_status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('membership_status', $member->membership_status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Hidden defaults for Youth Church -->
                        <input type="hidden" name="membership_status" value="{{ $member->membership_status ?? 'active' }}">
                    @endif
                </div>

                <div class="pt-5 border-t border-gray-200">
                    <div class="flex justify-end gap-x-3">
                        <a href="{{ route('members.index') }}"
                           class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Member
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection