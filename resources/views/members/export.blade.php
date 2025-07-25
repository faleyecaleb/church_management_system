@extends('layouts.admin')

@section('title', 'Export Members')
@section('header', 'Export Members')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-blue-600">
            <h2 class="text-2xl font-bold text-white">Export Members</h2>
            <p class="mt-1 text-green-100">Export member data to Excel or CSV format</p>
        </div>

        <div class="p-6">
            <!-- Export Form -->
            <form id="exportForm" class="space-y-6">
                @csrf

                <!-- Filters Section -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Export Filters</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Membership Status Filter -->
                        <div>
                            <label for="membership_status" class="block text-sm font-medium text-gray-700 mb-2">
                                Membership Status
                            </label>
                            <select name="membership_status" id="membership_status" 
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Statuses</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                        <!-- Gender Filter -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                                Gender
                            </label>
                            <select name="gender" id="gender" 
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div>
                            <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                                Department
                            </label>
                            <select name="department" id="department" 
                                    class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}">{{ ucfirst(str_replace('_', ' ', $dept)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date From -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">
                                Member Since (From)
                            </label>
                            <input type="date" name="date_from" id="date_from" 
                                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">
                                Member Since (To)
                            </label>
                            <input type="date" name="date_to" id="date_to" 
                                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Search
                            </label>
                            <input type="text" name="search" id="search" placeholder="Name or email..."
                                   class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <!-- Export Preview -->
                <div id="exportPreview" class="bg-blue-50 border border-blue-200 rounded-lg p-4 hidden">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p id="previewMessage" class="text-sm font-medium text-blue-800"></p>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">📄 Export Format</h4>
                    <div class="flex space-x-4">
                        <button type="button" id="exportExcelBtn" 
                                class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export to Excel
                        </button>
                        
                        <button type="button" id="exportCsvBtn" 
                                class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export to CSV
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button type="button" id="previewBtn" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview Export
                    </button>

                    <a href="{{ route('members.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Back to Members
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Export Information -->
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">📊 Export Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-medium text-gray-900 mb-2">Excel Export Includes:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Member ID and full details</li>
                    <li>• Contact information</li>
                    <li>• Membership status and dates</li>
                    <li>• Department assignments</li>
                    <li>• Professional formatting</li>
                    <li>• Styled headers and columns</li>
                </ul>
            </div>
            <div>
                <h4 class="font-medium text-gray-900 mb-2">CSV Export Features:</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Same data as Excel format</li>
                    <li>• Compatible with all spreadsheet apps</li>
                    <li>• Smaller file size</li>
                    <li>• Easy to import elsewhere</li>
                    <li>• UTF-8 encoding support</li>
                    <li>• Comma-separated values</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const exportPreview = document.getElementById('exportPreview');
    const previewMessage = document.getElementById('previewMessage');
    const form = document.getElementById('exportForm');

    // Handle preview
    previewBtn.addEventListener('click', function() {
        const formData = new FormData(form);
        
        fetch('{{ route("members.export.stats") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            previewMessage.textContent = data.message;
            exportPreview.classList.remove('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while previewing the export.');
        });
    });

    // Handle Excel export
    exportExcelBtn.addEventListener('click', function() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("members.export.excel") }}?' + params;
    });

    // Handle CSV export
    exportCsvBtn.addEventListener('click', function() {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData).toString();
        window.location.href = '{{ route("members.export.csv") }}?' + params;
    });

    // Auto-preview when filters change
    const filterInputs = form.querySelectorAll('select, input');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            exportPreview.classList.add('hidden');
        });
    });
});
</script>
@endsection