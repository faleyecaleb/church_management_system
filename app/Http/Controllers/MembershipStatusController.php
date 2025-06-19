<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MembershipStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MembershipStatusController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:manage members']);
    }

    /**
     * Display member's status history
     */
    public function index(Member $member)
    {
        $statuses = $member->membershipStatuses()
            ->with(['changedBy'])
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('membership.status.index', [
            'member' => $member,
            'statuses' => $statuses,
            'availableStatuses' => MembershipStatus::getStatuses()
        ]);
    }

    /**
     * Show form to change member's status
     */
    public function create(Member $member)
    {
        $currentStatus = $member->currentMembershipStatus();

        return view('membership.status.create', [
            'member' => $member,
            'currentStatus' => $currentStatus,
            'availableStatuses' => MembershipStatus::getStatuses()
        ]);
    }

    /**
     * Store a new status change
     */
    public function store(Request $request, Member $member)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'class_completed' => 'boolean',
            'transfer_church' => 'required_if:status,transferred|nullable|string|max:255',
            'transfer_date' => 'required_if:status,transferred|nullable|date',
            'renewal_date' => 'nullable|date|after:start_date'
        ]);

        try {
            DB::transaction(function () use ($member, $validated) {
                // End current status if exists
                $currentStatus = $member->currentMembershipStatus();
                if ($currentStatus) {
                    $currentStatus->update([
                        'end_date' => $validated['start_date']
                    ]);
                }

                // Create new status
                $member->membershipStatuses()->create([
                    'status' => $validated['status'],
                    'start_date' => $validated['start_date'],
                    'notes' => $validated['notes'] ?? null,
                    'changed_by' => Auth::id(),
                    'class_completed' => $validated['class_completed'] ?? false,
                    'transfer_church' => $validated['transfer_church'] ?? null,
                    'transfer_date' => $validated['transfer_date'] ?? null,
                    'renewal_date' => $validated['renewal_date'] ?? null
                ]);
            });

            return redirect()
                ->route('membership.status.index', $member)
                ->with('success', 'Member status updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update member status. ' . $e->getMessage());
        }
    }

    /**
     * Show status details
     */
    public function show(Member $member, MembershipStatus $status)
    {
        return view('membership.status.show', [
            'member' => $member,
            'status' => $status->load('changedBy')
        ]);
    }

    /**
     * Show form to edit status
     */
    public function edit(Member $member, MembershipStatus $status)
    {
        if (!$status->isCurrent()) {
            return back()->with('error', 'Only current status can be edited.');
        }

        return view('membership.status.edit', [
            'member' => $member,
            'status' => $status,
            'availableStatuses' => MembershipStatus::getStatuses()
        ]);
    }

    /**
     * Update status
     */
    public function update(Request $request, Member $member, MembershipStatus $status)
    {
        if (!$status->isCurrent()) {
            return back()->with('error', 'Only current status can be edited.');
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'start_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
            'class_completed' => 'boolean',
            'transfer_church' => 'required_if:status,transferred|nullable|string|max:255',
            'transfer_date' => 'required_if:status,transferred|nullable|date',
            'renewal_date' => 'nullable|date|after:start_date'
        ]);

        try {
            $status->update([
                'status' => $validated['status'],
                'start_date' => $validated['start_date'],
                'notes' => $validated['notes'] ?? null,
                'changed_by' => Auth::id(),
                'class_completed' => $validated['class_completed'] ?? false,
                'transfer_church' => $validated['transfer_church'] ?? null,
                'transfer_date' => $validated['transfer_date'] ?? null,
                'renewal_date' => $validated['renewal_date'] ?? null
            ]);

            return redirect()
                ->route('membership.status.index', $member)
                ->with('success', 'Status updated successfully.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update status. ' . $e->getMessage());
        }
    }

    /**
     * Delete status
     */
    public function destroy(Member $member, MembershipStatus $status)
    {
        if ($status->isCurrent()) {
            return back()->with('error', 'Cannot delete current status.');
        }

        try {
            $status->delete();
            return back()->with('success', 'Status deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete status. ' . $e->getMessage());
        }
    }
}