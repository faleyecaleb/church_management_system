@extends('layouts.admin')

@section('title', 'Complaint #'.$complaint->id)
@section('header', 'Complaint Details')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">#{{ $complaint->id }} - {{ $complaint->subject }}</h2>
                <p class="text-gray-500">Filed by {{ $complaint->complainant_display_name }} on {{ $complaint->created_at->format('M j, Y') }}</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('complaints.edit', $complaint) }}" class="px-3 py-2 rounded-lg bg-indigo-600 text-white">Edit</a>
                <form action="{{ route('complaints.resolve', $complaint) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="resolution_notes" value="Resolved">
                    <button type="submit" class="px-3 py-2 rounded-lg bg-green-600 text-white">Resolve</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Details</h3>
                <dl class="text-sm text-gray-700 space-y-2">
                    <div class="flex"><dt class="w-32 text-gray-500">Complainant</dt><dd>{{ $complaint->complainant_display_name }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Email</dt><dd>{{ $complaint->complainant_email ?? '—' }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Phone</dt><dd>{{ $complaint->complainant_phone ?? '—' }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Department</dt><dd>{{ $complaint->department ?? '—' }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Category</dt><dd>{{ \App\Models\Complaint::CATEGORIES[$complaint->category] }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Priority</dt><dd class="capitalize">{{ $complaint->priority }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Status</dt><dd class="capitalize">{{ str_replace('_',' ', $complaint->status) }}</dd></div>
                    <div class="flex"><dt class="w-32 text-gray-500">Assigned To</dt><dd>{{ $complaint->assignedTo->name ?? 'Unassigned' }}</dd></div>
                </dl>
            </div>
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Description</h3>
                <div class="prose max-w-none text-sm text-gray-800">{{ $complaint->description }}</div>
                @if($complaint->evidence_files)
                    <div class="mt-4">
                        <h4 class="font-medium">Evidence</h4>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($complaint->evidence_files as $i => $file)
                                <li>
                                    <a class="text-blue-600" href="{{ route('complaints.download-evidence', [$complaint, $i]) }}">{{ $file['original_name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-medium text-gray-900 mb-4">Add Response</h3>
        <form action="{{ route('complaints.add-response', $complaint) }}" method="POST" class="space-y-3">
            @csrf
            <textarea name="message" rows="3" class="w-full rounded-lg border-gray-300" placeholder="Write an update or response..."></textarea>
            <label class="inline-flex items-center space-x-2 text-sm">
                <input type="checkbox" name="is_internal" value="1" class="rounded"> <span>Internal only</span>
            </label>
            <div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white">Post Response</button>
            </div>
        </form>
        <div class="mt-6">
            <h3 class="font-medium text-gray-900 mb-2">Timeline</h3>
            <ul class="space-y-3">
                @foreach($complaint->responses as $response)
                    <li class="text-sm">
                        <div class="text-gray-700"><span class="px-2 py-0.5 rounded bg-gray-100">{{ $response->formatted_type }}</span> by {{ $response->user->name ?? 'System' }} • {{ $response->created_at->diffForHumans() }}</div>
                        <div class="text-gray-800">{{ $response->message }}</div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
