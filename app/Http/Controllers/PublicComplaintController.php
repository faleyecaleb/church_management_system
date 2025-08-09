<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Member;
use App\Models\MemberDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicComplaintController extends Controller
{
    /**
     * Show the public complaint form.
     */
    public function create()
    {
        $departments = MemberDepartment::getDepartmentOptions();
        
        return view('public.complaints.create', compact('departments'));
    }

    /**
     * Store a public complaint.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complainant_name' => 'required|string|max:255',
            'complainant_email' => 'required|email|max:255',
            'complainant_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Complaint::CATEGORIES)),
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'is_anonymous' => 'boolean',
            'evidence_files.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
            'g-recaptcha-response' => 'required', // Add reCAPTCHA validation if needed
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();
        
        // Set default priority for public complaints
        $data['priority'] = 'medium';
        $data['status'] = 'open';

        // Handle file uploads
        if ($request->hasFile('evidence_files')) {
            $files = [];
            foreach ($request->file('evidence_files') as $file) {
                $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('complaints/evidence', $filename, 'public');
                $files[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'filename' => $filename,
                    'path' => $path,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
            $data['evidence_files'] = $files;
        }

        // Try to find existing member by email
        if (!$data['is_anonymous'] && $data['complainant_email']) {
            $member = Member::where('email', $data['complainant_email'])->first();
            if ($member) {
                $data['member_id'] = $member->id;
            }
        }

        $complaint = Complaint::create($data);

        // Generate a reference number for the complainant
        $referenceNumber = 'CMP-' . str_pad($complaint->id, 6, '0', STR_PAD_LEFT);

        return view('public.complaints.success', compact('complaint', 'referenceNumber'));
    }

    /**
     * Show complaint status by reference number.
     */
    public function status(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'reference_number' => 'required|string',
                'email' => 'required|email',
            ]);

            // Extract ID from reference number (CMP-000001 -> 1)
            $id = (int) str_replace('CMP-', '', $request->reference_number);
            
            $complaint = Complaint::where('id', $id)
                ->where(function ($query) use ($request) {
                    $query->where('complainant_email', $request->email)
                          ->orWhereHas('member', function ($memberQuery) use ($request) {
                              $memberQuery->where('email', $request->email);
                          });
                })
                ->with(['publicResponses.user'])
                ->first();

            if (!$complaint) {
                return redirect()->back()
                    ->withErrors(['reference_number' => 'Complaint not found or email does not match.'])
                    ->withInput();
            }

            $referenceNumber = 'CMP-' . str_pad($complaint->id, 6, '0', STR_PAD_LEFT);

            return view('public.complaints.status', compact('complaint', 'referenceNumber'));
        }

        return view('public.complaints.check-status');
    }

    /**
     * Submit satisfaction rating for resolved complaint.
     */
    public function submitRating(Request $request, Complaint $complaint)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
            'email' => 'required|email',
        ]);

        // Verify the email matches
        $emailMatches = $complaint->complainant_email === $request->email ||
                       ($complaint->member && $complaint->member->email === $request->email);

        if (!$emailMatches || $complaint->status !== 'resolved') {
            abort(403, 'Unauthorized or complaint not resolved.');
        }

        $complaint->addSatisfactionRating($request->rating, $request->feedback);

        return redirect()->back()->with('success', 'Thank you for your feedback!');
    }
}