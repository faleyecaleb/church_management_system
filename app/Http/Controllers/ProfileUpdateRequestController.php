<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileUpdateRequestController extends Controller
{
    /**
     * Web Admin: List all pending profile update requests.
     */
    public function index()
    {
        $requests = ProfileUpdateRequest::with('member')
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('members.profile-requests', compact('requests'));
    }

    /**
     * Web Admin: Approve a request, merging its fields into the Member profile.
     */
    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $requestRecord = ProfileUpdateRequest::findOrFail($id);
            if ($requestRecord->status !== 'pending') {
                throw new \Exception('This request has already been processed.');
            }

            $member = $requestRecord->member;
            if (!$member) {
                throw new \Exception('Associated member profile was not found.');
            }

            // Merge fields
            $member->update($requestRecord->requested_data);

            // Mark Request as Approved
            $requestRecord->update([
                'status' => 'approved',
                'admin_notes' => 'Approved by admin on ' . now()->toFormattedDateString(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Profile update request approved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Web Admin: Reject a request.
     */
    public function reject(Request $request, $id)
    {
        try {
            $requestRecord = ProfileUpdateRequest::findOrFail($id);
            if ($requestRecord->status !== 'pending') {
                throw new \Exception('This request has already been processed.');
            }

            $requestRecord->update([
                'status' => 'rejected',
                'admin_notes' => $request->input('admin_notes', 'Rejected by admin.'),
            ]);

            return redirect()->back()->with('success', 'Profile update request rejected successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Mobile API: Create a new profile update request.
     */
    public function apiStore(Request $request)
    {
        try {
            $user = auth()->user();
            $member = null;
            if ($user instanceof \App\Models\Member) {
                $member = $user;
            } elseif ($user) {
                $member = \App\Models\Member::where('email', $user->email)->first();
            }

            if (!$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. No member profile found.',
                ], 403);
            }

            // Validate common editable profile fields
            $validated = $request->validate([
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'profession' => 'nullable|string|max:255',
                'city_of_residence' => 'nullable|string|max:255',
                'state_of_residence' => 'nullable|string|max:255',
            ]);

            // Keep only fields that actually changed or are filled
            $requestedData = array_filter($validated, function ($value, $key) use ($member) {
                return !is_null($value) && $member->{$key} !== $value;
            }, ARRAY_FILTER_USE_BOTH);

            if (empty($requestedData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes detected compared to your current profile.',
                ], 400);
            }

            // Create Profile Update Request
            $profileRequest = ProfileUpdateRequest::create([
                'member_id' => $member->id,
                'church_id' => $member->church_id,
                'requested_data' => $requestedData,
                'status' => 'pending',
            ]);

            // Optional: Log custom system notification here if desired
            return response()->json([
                'success' => true,
                'message' => 'Your profile update request has been submitted and is pending admin approval.',
                'data' => $profileRequest,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit request: ' . $e->getMessage(),
            ], 500);
        }
    }
}
