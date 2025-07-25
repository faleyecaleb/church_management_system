@extends('layouts.admin')

@section('title', 'Import Members')
@section('header', 'Bulk Import Members')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    
                    @if(session('import_details'))
                        <div class="mt-2">
                            <details class="text-sm text-green-700">
                                <summary class="cursor-pointer font-medium">View Details</summary>
                                <div class="mt-2 max-h-40 overflow-y-auto bg-green-100 p-2 rounded">
                                    @foreach(session('import_details') as $detail)
                                        <div class="mb-1">{{ $detail }}</div>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <h2 class="text-2xl font-bold text-white">Bulk Import Members</h2>
            <p class="mt-1 text-indigo-100">Import multiple members from a CSV file</p>
        </div>

        <div class="p-6">
            <!-- Instructions -->
            <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-lg font-medium text-blue-900 mb-3">📋 Import Instructions</h3>
                <div class="text-sm text-blue-800 space-y-2">
                    <p><strong>Step 1:</strong> Download the CSV template below to see the required format</p>
                    <p><strong>Step 2:</strong> Fill in your member data following the template format</p>
                    <p><strong>Step 3:</strong> Upload your CSV file using the form below</p>
                    <p><strong>Step 4:</strong> Review the preview and confirm the import</p>
                </div>
                
                <div class="mt-4 flex space-x-3">
                    <a href="{{ route('members.import.template') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download CSV Template
                    </a>
                    <a href="{{ route('members.import.template.excel') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Excel Template
                    </a>
                </div>
            </div>

            <!-- Import Form -->
            <form id="importForm" action="{{ route('members.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- File Upload -->
                <div>
                    <label for="import_file" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Import File *
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="import_file" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload CSV or Excel file</span>
                                    <input id="import_file" name="import_file" type="file" accept=".csv,.txt,.xlsx,.xls" class="sr-only" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">CSV, Excel files up to 10MB</p>
                        </div>
                    </div>
                    <div id="fileName" class="mt-2 text-sm text-gray-600 hidden"></div>
                </div>

                <!-- Import Options -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Import Options</h4>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="skip_duplicates" name="skip_duplicates" type="checkbox" value="1" checked 
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="skip_duplicates" class="ml-2 block text-sm text-gray-700">
                                Skip duplicate emails (recommended)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="update_existing" name="update_existing" type="checkbox" value="1"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="update_existing" class="ml-2 block text-sm text-gray-700">
                                Update existing members with same email
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Preview Section -->
                <div id="previewSection" class="hidden">
                    <h4 class="text-lg font-medium text-gray-900 mb-3">Preview Data</h4>
                    <div id="previewContent" class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-auto">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <button type="button" id="previewBtn" 
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors disabled:opacity-50" 
                            disabled>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview Data
                    </button>

                    <div class="flex space-x-3">
                        <a href="{{ route('members.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </a>
                        <button type="submit" id="importBtn"
                                class="inline-flex items-center px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50" 
                                disabled>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Import Members
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('import_file');
    const fileName = document.getElementById('fileName');
    const previewBtn = document.getElementById('previewBtn');
    const importBtn = document.getElementById('importBtn');
    const previewSection = document.getElementById('previewSection');
    const previewContent = document.getElementById('previewContent');

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            fileName.textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            fileName.classList.remove('hidden');
            previewBtn.disabled = false;
        } else {
            fileName.classList.add('hidden');
            previewBtn.disabled = true;
            importBtn.disabled = true;
            previewSection.classList.add('hidden');
        }
    });

    // Handle preview
    previewBtn.addEventListener('click', function() {
        const formData = new FormData();
        formData.append('import_file', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        fetch('{{ route("members.import.preview") }}', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPreview(data);
                importBtn.disabled = false;
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while previewing the file.');
        });
    });

    function displayPreview(data) {
        let html = `<div class="mb-3 text-sm text-gray-600">
            <strong>Total rows to import:</strong> ${data.total_rows}
            <br><strong>Preview (first 10 rows):</strong>
        </div>`;

        if (data.preview_data.length > 0) {
            html += '<div class="overflow-x-auto"><table class="min-w-full text-xs border border-gray-200">';
            
            // Headers
            html += '<thead class="bg-gray-100"><tr>';
            data.headers.forEach(header => {
                html += `<th class="px-2 py-1 border border-gray-200 text-left font-medium">${header}</th>`;
            });
            html += '</tr></thead>';

            // Data rows
            html += '<tbody>';
            data.preview_data.forEach(row => {
                html += '<tr class="hover:bg-gray-50">';
                data.headers.forEach(header => {
                    html += `<td class="px-2 py-1 border border-gray-200">${row[header] || ''}</td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table></div>';
        } else {
            html += '<p class="text-red-600">No valid data found in the CSV file.</p>';
        }

        previewContent.innerHTML = html;
        previewSection.classList.remove('hidden');
    }
});
</script>
@endsection