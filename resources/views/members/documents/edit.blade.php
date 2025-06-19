@extends('layouts.admin')

@section('title', 'Edit Document')
@section('header', 'Edit Member Document')

@section('content')
<div class="max-w-7xl mx-auto py-6">
    <x-success-message />

    <form action="{{ route('members.documents.update', [$member, $document]) }}" method="POST" enctype="multipart/form-data" class="space-y-8 divide-y divide-gray-200">
        @csrf
        @method('PUT')
        <x-validation-errors />

        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Document Information</h3>
                    <p class="mt-1 text-sm text-gray-500">Update the details of the document.</p>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Document title</label>
                        <div class="mt-1">
                            <input type="text" name="title" id="title" value="{{ old('title', $document->title) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <div class="mt-1">
                            <textarea name="description" id="description" rows="3"
                                      class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('description', $document->description) }}</textarea>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="document_type" class="block text-sm font-medium text-gray-700">Document type</label>
                        <div class="mt-1">
                            <select name="document_type" id="document_type"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="identification" {{ old('document_type', $document->document_type) === 'identification' ? 'selected' : '' }}>Identification</option>
                                <option value="certificate" {{ old('document_type', $document->document_type) === 'certificate' ? 'selected' : '' }}>Certificate</option>
                                <option value="medical" {{ old('document_type', $document->document_type) === 'medical' ? 'selected' : '' }}>Medical Record</option>
                                <option value="other" {{ old('document_type', $document->document_type) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="document" class="block text-sm font-medium text-gray-700">Update document file</label>
                        <div class="mt-1">
                            <input type="file" name="document" id="document"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0 file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">PDF, DOC, DOCX up to 10MB</p>
                        @if($document->file_path)
                            <p class="mt-2 text-sm text-gray-500">Current file: {{ basename($document->file_path) }}</p>
                        @endif
                    </div>

                    <div class="sm:col-span-3">
                        <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue date</label>
                        <div class="mt-1">
                            <input type="date" name="issue_date" id="issue_date" value="{{ old('issue_date', $document->issue_date?->format('Y-m-d')) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    <div class="sm:col-span-3">
                        <label for="expiry_date" class="block text-sm font-medium text-gray-700">Expiry date</label>
                        <div class="mt-1">
                            <input type="date" name="expiry_date" id="expiry_date" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}"
                                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>

                    @can('verify documents')
                    <div class="sm:col-span-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="checkbox" name="is_verified" id="is_verified" value="1"
                                       {{ old('is_verified', $document->is_verified) ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_verified" class="font-medium text-gray-700">Verify document</label>
                                <p class="text-gray-500">Mark this document as verified.</p>
                            </div>
                        </div>
                    </div>
                    @endcan
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