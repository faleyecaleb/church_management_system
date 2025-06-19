<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmergencyContactController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:member.view')->only('index', 'show');
        $this->middleware('permission:member.create')->only('create', 'store');
        $this->middleware('permission:member.edit')->only('edit', 'update');
        $this->middleware('permission:member.delete')->only('destroy');
    }

    /**
     * Show the form for creating a new emergency contact.
     */
    public function create(Member $member)
    {
        return view('members.emergency-contacts.create', compact('member'));
    }

    /**
     * Store a newly created emergency contact in storage.
     */
    public function store(Request $request, Member $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'is_primary' => 'boolean',
        ]);

        DB::transaction(function () use ($member, $validated) {
            // If this contact is set as primary, unset any existing primary contact
            if (!empty($validated['is_primary'])) {
                $member->emergencyContacts()->where('is_primary', true)->update(['is_primary' => false]);
            }
            // If this is the first contact, make it primary regardless of input
            elseif ($member->emergencyContacts()->count() === 0) {
                $validated['is_primary'] = true;
            }

            $member->emergencyContacts()->create($validated);
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Emergency contact added successfully.');
    }

    /**
     * Show the form for editing the specified emergency contact.
     */
    public function edit(Member $member, EmergencyContact $emergencyContact)
    {
        return view('members.emergency-contacts.edit', [
            'member' => $member,
            'emergencyContact' => $emergencyContact,
        ]);
    }

    /**
     * Update the specified emergency contact in storage.
     */
    public function update(Request $request, Member $member, EmergencyContact $emergencyContact)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'is_primary' => 'boolean',
        ]);

        DB::transaction(function () use ($member, $emergencyContact, $validated) {
            // Handle primary contact changes
            if (!empty($validated['is_primary']) && !$emergencyContact->is_primary) {
                // Unset current primary contact if this contact is being set as primary
                $member->emergencyContacts()->where('is_primary', true)->update(['is_primary' => false]);
            } elseif (empty($validated['is_primary']) && $emergencyContact->is_primary) {
                // If unsetting primary status, make the oldest contact primary
                $validated['is_primary'] = true; // Keep this one primary if it's the only contact
                if ($member->emergencyContacts()->count() > 1) {
                    $newPrimary = $member->emergencyContacts()
                        ->where('id', '!=', $emergencyContact->id)
                        ->orderBy('created_at')
                        ->first();
                    if ($newPrimary) {
                        $newPrimary->update(['is_primary' => true]);
                        $validated['is_primary'] = false;
                    }
                }
            }

            $emergencyContact->update($validated);
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Emergency contact updated successfully.');
    }

    /**
     * Remove the specified emergency contact from storage.
     */
    public function destroy(Member $member, EmergencyContact $emergencyContact)
    {
        DB::transaction(function () use ($member, $emergencyContact) {
            // If deleting primary contact, make the oldest remaining contact primary
            if ($emergencyContact->is_primary) {
                $newPrimary = $member->emergencyContacts()
                    ->where('id', '!=', $emergencyContact->id)
                    ->orderBy('created_at')
                    ->first();
                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }

            $emergencyContact->delete();
        });

        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Emergency contact deleted successfully.');
    }
}