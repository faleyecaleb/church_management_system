@extends('layouts.admin')

@section('title', 'Settings')
@section('header', 'System Settings')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Search and Filter -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 mb-6 hover:bg-white/90 transition-all duration-300">
        <form action="{{ route('settings.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Settings</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="w-48">
                <label for="group" class="block text-sm font-medium text-gray-700 mb-1">Filter by Group</label>
                <select name="group" id="group" 
                    class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <option value="">All Groups</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>{{ ucfirst($group) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Settings List -->
    <div class="bg-white/70 backdrop-blur-sm rounded-2xl border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white/50">
                    @forelse($settings as $setting)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $setting->key }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($setting->value, 50) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst($setting->group) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($setting->description, 100) }}</td>
                            <td class="px-6 py-4 text-sm font-medium space-x-2">
                                <a href="{{ route('settings.edit', $setting) }}" class="text-primary-600 hover:text-primary-900">Edit</a>
                                <a href="{{ route('settings.show', $setting) }}" class="text-gray-600 hover:text-gray-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No settings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $settings->links() }}
        </div>
    </div>
</div>
@endsection