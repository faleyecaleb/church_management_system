@extends('layouts.admin')

@section('title', 'Members')
@section('header', 'Church Members')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Member Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Members</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $members->total() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Active Members</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $members->where('membership_status', 'active')->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Deleted Members</dt>
                            <dd class="text-3xl font-semibold text-gray-900">{{ $members->onlyTrashed()->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Header with Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Church Members</h1>
            <p class="mt-1 text-sm text-gray-600">Manage your church member database</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <!-- Export button - shows if route exists -->
            @if(Route::has('members.export.form'))
                <a href="{{ route('members.export.form') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-xl cursor-not-allowed font-medium" title="Export feature - routes need to be added">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export (Coming Soon)
                </span>
            @endif
            
            <!-- Import button - shows if route exists -->
            @if(Route::has('members.import.form'))
                <a href="{{ route('members.import.form') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Bulk Import
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 bg-gray-400 text-white rounded-xl cursor-not-allowed font-medium" title="Import feature - routes need to be added">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Bulk Import (Coming Soon)
                </span>
            @endif
            
            <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Member
            </a>
        </div>
    </div>

    <div class="flex justify-between items-center mb-6">
        <div class="flex-1 pr-4">
            <form class="space-y-4">
                <!-- Search and Name Filter -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="search" name="search" id="search" value="{{ request('search') }}"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                   placeholder="Search by name or email...">
                        </div>
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('members.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Clear
                    </a>
                </div>
                
                <!-- Filter Options -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="deceased" {{ request('status') == 'deceased' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="department" class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                        <select name="department" id="department" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                            <option value="">All Departments</option>
                            <option value="Media" {{ request('department') == 'Media' ? 'selected' : '' }}>Media</option>
                            <option value="Choir" {{ request('department') == 'Choir' ? 'selected' : '' }}>Choir</option>
                            <option value="Ushers" {{ request('department') == 'Ushers' ? 'selected' : '' }}>Ushers</option>
                            <option value="Dance" {{ request('department') == 'Dance' ? 'selected' : '' }}>Dance</option>
                            <option value="Prayer" {{ request('department') == 'Prayer' ? 'selected' : '' }}>Prayer</option>
                            <option value="Lost but Found" {{ request('department') == 'Lost but Found' ? 'selected' : '' }}>Lost but Found</option>
                            <option value="Drama" {{ request('department') == 'Drama' ? 'selected' : '' }}>Drama</option>
                            <option value="Sanctuary" {{ request('department') == 'Sanctuary' ? 'selected' : '' }}>Sanctuary</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="gender" class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
                        <select name="gender" id="gender" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                            <option value="">All Genders</option>
                            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort" class="block text-xs font-medium text-gray-700 mb-1">Sort By</label>
                        <select name="sort" id="sort" class="block w-full pl-3 pr-10 py-2 text-sm border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="created_desc" {{ request('sort') == 'created_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        {{-- @can('member.create') --}}
        <div class="flex-shrink-0">
            <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Member
            </a>
        </div>
        {{-- @endcan --}}
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($members as $member)
            <li class="hover:bg-gray-50">
                <div class="flex items-center px-4 py-4 sm:px-6">
                    <div class="min-w-0 flex-1 flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-12 w-12 rounded-full" 
                                 src="{{ $member->profile_photo_url }}" 
                                 alt="{{ $member->full_name }}">
                        </div>
                        <div class="min-w-0 flex-1 px-4">
                            <div>
                                <p class="text-sm font-medium text-indigo-600 truncate">{{ $member->full_name }}</p>
                                <p class="mt-1 flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                                    </svg>
                                    <span class="truncate">{{ $member->email }}</span>
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 space-x-2">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $member->membership_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($member->membership_status) }}
                                </span>
                                @if($member->departments->count() > 0)
                                    @foreach($member->departments as $dept)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        {{ $dept->department }}
                                    </span>
                                    @endforeach
                                @endif
                                @if($member->baptism_date)
                                <span>Baptized on {{ $member->baptism_date->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        {{-- @can('member.view') --}}
                        <a href="{{ route('members.show', $member) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </a>
                        {{-- @endcan --}}
                        {{-- @can('member.update') --}}
                        <a href="{{ route('members.edit', $member) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-yellow-600 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <svg class="-ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </a>
                        {{-- @endcan --}}
                        {{-- @can('member.delete') --}}
                        <form action="{{ route('members.destroy', $member) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-red-600 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this member?')">
                                <svg class="-ml-1 mr-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete
                            </button>
                        </form>
                        {{-- @endcan --}}
                    </div>
                </div>
            </li>
            @empty
            <li class="px-4 py-4 sm:px-6">
                <p class="text-gray-500 text-center">No members found.</p>
            </li>
            @endforelse
        </ul>
    </div>

    <div class="mt-4">
        {{ $members->appends(request()->query())->links() }}
    </div>
</div>
@endsection