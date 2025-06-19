<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MemberDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:member.view')->only('index', 'show', 'download');
        $this->middleware('permission:member.create')->only('create', 'store');
        $this->middleware('permission:member.edit')->only('edit', 'update');
        $this->middleware('permission:member.delete')->only('destroy');
        $this->middleware('permission:verify documents')->only('verify');
    }

    /**
     * Show the form for creating a new document.
     */
    public function create(Member $member)
    {
        return view('members.documents.create', compact('member'));
    }

    /**
     * Store a newly created document in storage.
     */
    public function store(Request $request, Member $member)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:identification,certificate,medical,other',
            'document' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'is_verified' => 'boolean',
        ]);

        // Store the file
        $path = $request->file('document')->store('member-documents/' . $member->id, 'public');
        $size = $request->file('document')->getSize();
        $type = $request->file('document')->getClientMimeType();

        // Create document record
        $document = $member->documents()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'file_type' => $type,
            'file_size' => $size,
            'issue_date' => $validated['issue_date'],
            'expiry_date' => $validated['expiry_date'],
            'is_verified' => $request->user()->can('verify documents') ? ($validated['is_verified'] ?? false) : false,
            'verified_by' => $request->user()->can('verify documents') && ($validated['is_verified'] ?? false) ? $request->user()->id : null,
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Document uploaded successfully.');
    }

    /**
     * Show the form for editing the specified document.
     */
    public function edit(Member $member, MemberDocument $document)
    {
        return view('members.documents.edit', [
            'member' => $member,
            'document' => $document,
        ]);
    }

    /**
     * Update the specified document in storage.
     */
    public function update(Request $request, Member $member, MemberDocument $document)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'document_type' => 'required|string|in:identification,certificate,medical,other',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB max
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'is_verified' => 'boolean',
        ]);

        // Handle file update if new file is uploaded
        if ($request->hasFile('document')) {
            // Delete old file
            if ($document->file_path) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Store new file
            $path = $request->file('document')->store('member-documents/' . $member->id, 'public');
            $size = $request->file('document')->getSize();
            $type = $request->file('document')->getClientMimeType();

            $validated['file_path'] = $path;
            $validated['file_type'] = $type;
            $validated['file_size'] = $size;
        }

        // Handle verification status
        if ($request->user()->can('verify documents')) {
            $wasVerified = $document->is_verified;
            $nowVerified = $validated['is_verified'] ?? false;

            if (!$wasVerified && $nowVerified) {
                $validated['verified_by'] = $request->user()->id;
            } elseif ($wasVerified && !$nowVerified) {
                $validated['verified_by'] = null;
            }
        } else {
            unset($validated['is_verified']);
        }

        $document->update($validated);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified document from storage.
     */
    public function destroy(Member $member, MemberDocument $document)
    {
        // Delete the file
        if ($document->file_path) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Download the specified document.
     */
    public function download(Member $member, MemberDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->with('error', 'Document file not found.');
        }

        return Storage::disk('public')->download(
            $document->file_path,
            $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Toggle verification status of the document.
     */
    public function verify(Request $request, Member $member, MemberDocument $document)
    {
        $document->update([
            'is_verified' => !$document->is_verified,
            'verified_by' => !$document->is_verified ? $request->user()->id : null,
        ]);

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Document verification status updated successfully.');
    }
}