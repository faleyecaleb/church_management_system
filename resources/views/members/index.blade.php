@extends('layouts.admin')

@section('title', 'Members')
@section('header', 'Church Members')

@section('content')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --glass-shadow: rgba(0, 0, 0, 0.1);
        --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    /* Animated Background */
    .members-container {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }
    
    .members-container::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        animation: rotate 20s linear infinite;
    }
    
    .members-container::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: 
            radial-gradient(circle at 20% 50%, rgba(245, 87, 108, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(79, 172, 254, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }
    
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.3); }
        50% { box-shadow: 0 0 40px rgba(102, 126, 234, 0.6); }
    }
    
    /* Glass Effect */
    .glass {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }
    
    .glass-strong {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.15);
    }
    
    /* Content Wrapper */
    .content-wrapper {
        position: relative;
        z-index: 1;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem;
    }
    
    /* Stat Cards */
    .stat-card {
        animation: slideInUp 0.6s ease-out;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(102, 126, 234, 0.3);
    }
    
    .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: float 3s ease-in-out infinite;
    }
    
    .stat-card:nth-child(2) .stat-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        animation-delay: 0.5s;
    }
    
    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #f5576c 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    /* Buttons */
    .glass-button {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .glass-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .glass-button:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .glass-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }
    
    /* Search Bar */
    .search-container {
        position: relative;
    }
    
    .search-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(102, 126, 234, 0.5);
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        outline: none;
    }
    
    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }
    
    /* Filter Selects */
    .filter-select {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        transition: all 0.3s ease;
    }
    
    .filter-select:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(102, 126, 234, 0.5);
        outline: none;
    }
    
    .filter-select option {
        background: #1a1a2e;
        color: #fff;
    }
    
    /* Member Cards Grid */
    .members-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .member-card {
        animation: slideInUp 0.6s ease-out;
        animation-fill-mode: both;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
    }
    
    .member-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 1rem;
        padding: 2px;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(245, 87, 108, 0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none; /* Allow clicks to pass through */
        z-index: 0;
    }
    
    .member-card:hover::before {
        opacity: 1;
    }
    
    .member-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
    }
    
    /* Stagger animation for cards */
    .member-card:nth-child(1) { animation-delay: 0.1s; }
    .member-card:nth-child(2) { animation-delay: 0.2s; }
    .member-card:nth-child(3) { animation-delay: 0.3s; }
    .member-card:nth-child(4) { animation-delay: 0.4s; }
    .member-card:nth-child(5) { animation-delay: 0.5s; }
    .member-card:nth-child(6) { animation-delay: 0.6s; }
    
    /* Profile Photo */
    .profile-photo-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto;
    }
    
    .profile-photo-wrapper::before {
        content: '';
        position: absolute;
        inset: -3px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #f5576c, #4facfe);
        animation: rotate 3s linear infinite;
        z-index: -1;
    }
    
    .profile-photo {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid rgba(255, 255, 255, 0.2);
        object-fit: cover;
    }
    
    /* Badges */
    .badge-glass {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .badge-glass:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: scale(1.05);
    }
    
    .badge-active {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
        border-color: rgba(16, 185, 129, 0.4);
        color: #10b981;
        box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
    }
    
    .badge-inactive {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2));
        border-color: rgba(239, 68, 68, 0.4);
        color: #ef4444;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
    }
    
    .badge-newcomer {
        background: linear-gradient(135deg, rgba(251, 146, 60, 0.2), rgba(249, 115, 22, 0.2));
        border-color: rgba(251, 146, 60, 0.4);
        color: #fb923c;
        box-shadow: 0 0 15px rgba(251, 146, 60, 0.3);
    }
    
    .badge-member {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));
        border-color: rgba(59, 130, 246, 0.4);
        color: #3b82f6;
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.3);
    }
    
    .badge-department {
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(147, 51, 234, 0.2));
        border-color: rgba(168, 85, 247, 0.4);
        color: #a855f7;
        box-shadow: 0 0 15px rgba(168, 85, 247, 0.3);
    }
    
    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        margin-top: 1rem;
        position: relative;
        z-index: 10; /* Ensure buttons are above other elements */
    }
    
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        cursor: pointer;
        position: relative;
        z-index: 10;
    }
    
    .action-btn:hover {
        transform: translateY(-2px) scale(1.1);
    }
    
    /* Tooltip Styling */
    .action-btn::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-8px);
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(10px);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        white-space: nowrap;
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 1000;
    }
    
    .action-btn::before {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-2px);
        border: 5px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
        opacity: 0;
        pointer-events: none;
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .action-btn:hover::after,
    .action-btn:hover::before {
        opacity: 1;
    }
    
    .action-btn-view {
        color: #667eea;
    }
    
    .action-btn-view:hover {
        background: rgba(102, 126, 234, 0.2);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .action-btn-edit {
        color: #fbbf24;
    }
    
    .action-btn-edit:hover {
        background: rgba(251, 191, 36, 0.2);
        box-shadow: 0 5px 15px rgba(251, 191, 36, 0.4);
    }
    
    .action-btn-delete {
        color: #ef4444;
    }
    
    .action-btn-delete:hover {
        background: rgba(239, 68, 68, 0.2);
        box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
    }
    
    .action-btn-promote {
        color: #10b981;
    }
    
    .action-btn-promote:hover {
        background: rgba(16, 185, 129, 0.2);
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
    }
    
    /* Pagination */
    .pagination-wrapper {
        margin-top: 2rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .members-grid {
            grid-template-columns: 1fr;
        }
        
        .content-wrapper {
            padding: 1rem;
        }
        
        .stat-card {
            margin-bottom: 1rem;
        }
    }
    
    @media (min-width: 769px) and (max-width: 1024px) {
        .members-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .empty-state svg {
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
        opacity: 0.3;
    }
</style>

<div class="members-container">
    <div class="content-wrapper">
        <!-- Member Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center">
                    <div class="stat-icon rounded-xl p-4 mr-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-300 mb-1">Total Members</p>
                        <p class="text-4xl font-bold text-white">{{ $members->total() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="stat-card glass rounded-2xl p-6">
                <div class="flex items-center">
                    <div class="stat-icon rounded-xl p-4 mr-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-300 mb-1">Active Members</p>
                        <p class="text-4xl font-bold text-white">{{ $members->where('membership_status', 'active')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header with Actions -->
        <div class="glass rounded-2xl p-6 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold gradient-text mb-2">Church Members</h1>
                    <p class="text-gray-300">Manage your church member database</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    @if(Route::has('members.export.form'))
                        <a href="{{ route('members.export.form') }}" class="glass-button glass rounded-xl px-5 py-2.5 text-white font-medium inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export
                        </a>
                    @endif
                    
                    @if(Route::has('members.import.form'))
                        <a href="{{ route('members.import.form') }}" class="glass-button glass rounded-xl px-5 py-2.5 text-white font-medium inline-flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Import
                        </a>
                    @endif
                    
                    <a href="{{ route('members.create') }}" class="glass-button glass-strong rounded-xl px-5 py-2.5 text-white font-medium inline-flex items-center" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.3), rgba(118, 75, 162, 0.3));">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Member
                    </a>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="glass rounded-2xl p-6 mb-6">
            <form class="space-y-4">
                <!-- Search Bar -->
                <div class="search-container">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="search" name="search" id="search" value="{{ request('search') }}"
                               class="search-input block w-full pl-12 pr-4 py-3 rounded-xl text-white placeholder-gray-400"
                               placeholder="Search by name or email...">
                    </div>
                </div>
                
                <!-- Filter Options -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-300 mb-2">Status</label>
                        <select name="status" id="status" class="filter-select block w-full px-4 py-2.5 rounded-xl">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="deceased" {{ request('status') == 'deceased' ? 'selected' : '' }}>Deceased</option>
                        </select>
                    </div>

                    <div>
                        <label for="member_type" class="block text-xs font-medium text-gray-300 mb-2">Member Type</label>
                        <select name="member_type" id="member_type" class="filter-select block w-full px-4 py-2.5 rounded-xl">
                            <option value="">All Types</option>
                            <option value="new_comer" {{ request('member_type') == 'new_comer' ? 'selected' : '' }}>New Comer</option>
                            <option value="main_member" {{ request('member_type') == 'main_member' ? 'selected' : '' }}>Main Member</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="department" class="block text-xs font-medium text-gray-300 mb-2">Department</label>
                        <select name="department" id="department" class="filter-select block w-full px-4 py-2.5 rounded-xl">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="gender" class="block text-xs font-medium text-gray-300 mb-2">Gender</label>
                        <select name="gender" id="gender" class="filter-select block w-full px-4 py-2.5 rounded-xl">
                            <option value="">All Genders</option>
                            <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort" class="block text-xs font-medium text-gray-300 mb-2">Sort By</label>
                        <select name="sort" id="sort" class="filter-select block w-full px-4 py-2.5 rounded-xl">
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                            <option value="created_desc" {{ request('sort') == 'created_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="created_asc" {{ request('sort') == 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="submit" class="glass-button glass rounded-xl px-6 py-2.5 text-white font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                        </svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('members.index') }}" class="glass-button glass rounded-xl px-6 py-2.5 text-white font-medium inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Clear All
                    </a>
                </div>
            </form>
        </div>

        <!-- Members Grid -->
        @if($members->count() > 0)
            <div class="members-grid">
                @foreach($members as $member)
                <div class="member-card glass rounded-2xl p-6">
                    <!-- Profile Photo -->
                    <div class="profile-photo-wrapper mb-4">
                        <img class="profile-photo" 
                             src="{{ $member->profile_photo_url }}" 
                             alt="{{ $member->full_name }}">
                    </div>
                    
                    <!-- Member Info -->
                    <div class="text-center mb-4">
                        <h3 class="text-xl font-bold text-white mb-1">{{ $member->full_name }}</h3>
                        <p class="text-sm text-gray-400 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            {{ $member->email }}
                        </p>
                    </div>
                    
                    <!-- Badges -->
                    <div class="flex flex-wrap gap-2 justify-center mb-4">
                        <span class="badge-glass {{ $member->membership_status === 'active' ? 'badge-active' : 'badge-inactive' }}">
                            {{ ucfirst($member->membership_status) }}
                        </span>
                        
                        <span class="badge-glass {{ $member->isNewComer() ? 'badge-newcomer' : 'badge-member' }}">
                            {{ $member->isNewComer() ? 'New Comer' : 'Main Member' }}
                        </span>
                        
                        @forelse($member->departments as $dept)
                            <span class="badge-glass badge-department">
                                {{ $dept->department->name ?? 'N/A' }}
                            </span>
                        @empty
                        @endforelse
                    </div>
                    
                    @if($member->baptism_date)
                    <p class="text-xs text-gray-400 text-center mb-4">
                        Baptized: {{ $member->baptism_date->format('M d, Y') }}
                    </p>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="{{ route('members.show', $member) }}" class="action-btn action-btn-view" data-tooltip="View Member">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        
                        <a href="{{ route('members.edit', $member) }}" class="action-btn action-btn-edit" data-tooltip="Edit Member">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </a>
                        
                        @if($member->isNewComer())
                        <form action="{{ route('members.promote', $member) }}" method="POST" class="inline-block">
                            @csrf
                            <button type="submit" class="action-btn action-btn-promote" data-tooltip="Promote to Main Member" onclick="return confirm('Promote this member to Main Member?')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                </svg>
                            </button>
                        </form>
                        @endif
                        
                        <form action="{{ route('members.destroy', $member) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn action-btn-delete" data-tooltip="Delete Member" onclick="return confirm('Are you sure you want to delete this member?')">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="glass rounded-2xl empty-state">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="text-xl font-semibold mb-2">No members found</h3>
                <p>Try adjusting your search or filter criteria</p>
            </div>
        @endif

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $members->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection