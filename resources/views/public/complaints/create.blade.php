@extends('layouts.admin')

@section('title', 'Submit a Complaint')
@section('header', 'Submit a Complaint')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('public.complaints.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4">
                    <div class="text-red-700 font-semibold mb-2">Please fix the following errors:</div>
                    <ul class="list-disc list-inside text-red-700 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <label for="is_anonymous" class="ml-2 text-sm text-gray-700">Submit anonymously</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Name (optional)</label>
                    <input type="text" name="complainant_name" value="{{ old('complainant_name') }}" class="w-full rounded-lg border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your Email (optional)</label>
                    <input type="email" name="complainant_email" value="{{ old('complainant_email') }}" class="w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Department (optional)</label>
                    <select name="department" class="w-full rounded-lg border-gray-300">
                        <option value="">-- None --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department }}" {{ old('department') === $department ? 'selected' : '' }}>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="w-full rounded-lg border-gray-300">
                        @foreach(\App\Models\Complaint::CATEGORIES as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="6" class="w-full rounded-lg border-gray-300">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Evidence Files (optional)</label>
                    <input type="file" name="evidence_files[]" multiple class="w-full rounded-lg border-gray-300" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt">
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white">Submit Complaint</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
