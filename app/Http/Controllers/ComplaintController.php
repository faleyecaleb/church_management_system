<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintResponse;
use App\Models\Member;
use App\Models\User;
use App\Models\MemberDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of complaints.
     */
    public function index(Request $request)
    {
        $query = Complaint::with(['member', 'assignedTo', 'resolvedBy'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Special filters
        if ($request->filter === 'overdue') {
            $query->overdue();
        } elseif ($request->filter === 'urgent') {
            $query->urgent();
        } elseif ($request->filter === 'open') {
            $query->open();
        } elseif ($request->filter === 'my_assignments') {
            $query->assignedTo(auth()->id());
        }

        $complaints = $query->paginate(20)->withQueryString();

        // Get filter options
        $departments = MemberDepartment::getDepartmentOptions();
        $users = User::orderBy('name')->get();
        $stats = Complaint::getStats();

        return view('complaints.index', compact(
            'complaints',
            'departments',
            'users',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new complaint.
     */
    public function create()
    {
        $members = Member::orderBy('first_name')->get();
        $departments = MemberDepartment::getDepartmentOptions();
        $users = User::orderBy('name')->get();

        return view('complaints.create', compact('members', 'departments', 'users'));
    }

    /**
     * Store a newly created complaint.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|exists:members,id',
            'complainant_name' => 'required_if:member_id,null|string|max:255',
            'complainant_email' => 'nullable|email|max:255',
            'complainant_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Complaint::CATEGORIES)),
            'priority' => 'required|in:' . implode(',', array_keys(Complaint::PRIORITIES)),
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'is_anonymous' => 'boolean',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date|after:today',
            'evidence_files.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

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

        $complaint = Complaint::create($data);

        // Add initial response if assigned
        if ($complaint->assigned_to) {
            $complaint->addResponse([
                'user_id' => auth()->id(),
                'response_type' => 'assignment',
                'message' => "Complaint assigned to " . $complaint->assignedTo->name,
                'is_internal' => true,
                'metadata' => ['assigned_to' => $complaint->assigned_to],
            ]);
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint created successfully.');
    }

    /**
     * Display the specified complaint.
     */
    public function show(Complaint $complaint)
    {
        $complaint->load([
            'member',
            'assignedTo',
            'resolvedBy',
            'escalatedTo',
            'responses.user'
        ]);

        $users = User::orderBy('name')->get();

        return view('complaints.show', compact('complaint', 'users'));
    }

    /**
     * Show the form for editing the complaint.
     */
    public function edit(Complaint $complaint)
    {
        $members = Member::orderBy('first_name')->get();
        $departments = MemberDepartment::getDepartmentOptions();
        $users = User::orderBy('name')->get();

        return view('complaints.edit', compact('complaint', 'members', 'departments', 'users'));
    }

    /**
     * Update the specified complaint.
     */
    public function update(Request $request, Complaint $complaint)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'nullable|exists:members,id',
            'complainant_name' => 'required_if:member_id,null|string|max:255',
            'complainant_email' => 'nullable|email|max:255',
            'complainant_phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'category' => 'required|in:' . implode(',', array_keys(Complaint::CATEGORIES)),
            'priority' => 'required|in:' . implode(',', array_keys(Complaint::PRIORITIES)),
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'is_anonymous' => 'boolean',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $oldAssignedTo = $complaint->assigned_to;
        $complaint->update($validator->validated());

        // Track assignment changes
        if ($oldAssignedTo !== $complaint->assigned_to) {
            if ($complaint->assigned_to) {
                $complaint->addResponse([
                    'user_id' => auth()->id(),
                    'response_type' => 'assignment',
                    'message' => "Complaint reassigned to " . $complaint->assignedTo->name,
                    'is_internal' => true,
                    'metadata' => [
                        'old_assigned_to' => $oldAssignedTo,
                        'new_assigned_to' => $complaint->assigned_to,
                    ],
                ]);
            } else {
                $complaint->addResponse([
                    'user_id' => auth()->id(),
                    'response_type' => 'assignment',
                    'message' => "Complaint unassigned",
                    'is_internal' => true,
                    'metadata' => ['old_assigned_to' => $oldAssignedTo],
                ]);
            }
        }

        return redirect()->route('complaints.show', $complaint)
            ->with('success', 'Complaint updated successfully.');
    }

    /**
     * Assign complaint to a user.
     */
    public function assign(Request $request, Complaint $complaint)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ]);

        $complaint->assign($request->assigned_to, auth()->id());

        if ($request->filled('message')) {
            $complaint->addResponse([
                'user_id' => auth()->id(),
                'response_type' => 'comment',
                'message' => $request->message,
                'is_internal' => true,
            ]);
        }

        return redirect()->back()->with('success', 'Complaint assigned successfully.');
    }

    /**
     * Escalate complaint.
     */
    public function escalate(Request $request, Complaint $complaint)
    {
        $request->validate([
            'escalated_to' => 'required|exists:users,id',
            'reason' => 'required|string',
        ]);

        $complaint->escalate($request->escalated_to, $request->reason, auth()->id());

        return redirect()->back()->with('success', 'Complaint escalated successfully.');
    }

    /**
     * Resolve complaint.
     */
    public function resolve(Request $request, Complaint $complaint)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $complaint->resolve($request->resolution_notes, auth()->id());

        return redirect()->back()->with('success', 'Complaint resolved successfully.');
    }

    /**
     * Update complaint status.
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Complaint::STATUSES)),
            'reason' => 'nullable|string',
        ]);

        $complaint->updateStatus($request->status, $request->reason, auth()->id());

        return redirect()->back()->with('success', 'Status updated successfully.');
    }

    /**
     * Add response to complaint.
     */
    public function addResponse(Request $request, Complaint $complaint)
    {
        $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $complaint->addResponse([
            'user_id' => auth()->id(),
            'response_type' => 'comment',
            'message' => $request->message,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        return redirect()->back()->with('success', 'Response added successfully.');
    }

    /**
     * Set follow-up for complaint.
     */
    public function setFollowUp(Request $request, Complaint $complaint)
    {
        $request->validate([
            'follow_up_date' => 'required|date|after:today',
            'message' => 'nullable|string',
        ]);

        $complaint->setFollowUp($request->follow_up_date, true);

        if ($request->filled('message')) {
            $complaint->addResponse([
                'user_id' => auth()->id(),
                'response_type' => 'follow_up',
                'message' => $request->message,
                'is_internal' => true,
                'metadata' => ['follow_up_date' => $request->follow_up_date],
            ]);
        }

        return redirect()->back()->with('success', 'Follow-up scheduled successfully.');
    }

    /**
     * Add satisfaction rating.
     */
    public function addSatisfactionRating(Request $request, Complaint $complaint)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
        ]);

        $complaint->addSatisfactionRating($request->rating, $request->feedback);

        return redirect()->back()->with('success', 'Satisfaction rating added successfully.');
    }

    /**
     * Download evidence file.
     */
    public function downloadEvidence(Complaint $complaint, $fileIndex)
    {
        if (!$complaint->evidence_files || !isset($complaint->evidence_files[$fileIndex])) {
            abort(404);
        }

        $file = $complaint->evidence_files[$fileIndex];
        $path = storage_path('app/public/' . $file['path']);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $file['original_name']);
    }

    /**
     * Export complaints data.
     */
    public function export(Request $request)
    {
        // This would implement export functionality
        // For now, return a simple response
        return response()->json(['message' => 'Export functionality to be implemented']);
    }

    /**
     * Delete complaint.
     */
    public function destroy(Complaint $complaint)
    {
        // Delete evidence files
        if ($complaint->evidence_files) {
            foreach ($complaint->evidence_files as $file) {
                Storage::disk('public')->delete($file['path']);
            }
        }

        $complaint->delete();

        return redirect()->route('complaints.index')
            ->with('success', 'Complaint deleted successfully.');
    }

    /**
     * Get dashboard stats for complaints.
     */
    public function dashboardStats()
    {
        $stats = Complaint::getStats();
        $recentComplaints = Complaint::with(['member', 'assignedTo'])
            ->recent(7)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_complaints' => $recentComplaints,
        ]);
    }
}