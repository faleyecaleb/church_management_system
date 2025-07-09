<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDepartment;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:member.view')->only(['index', 'show']);
        $this->middleware('permission:member.create')->only(['create', 'store']);
        $this->middleware('permission:member.update')->only(['edit', 'update']);
        $this->middleware('permission:member.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Member::query();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('membership_status', $request->input('status'));
        }

        if ($request->filled('department')) {
            $query->whereHas('departments', function ($q) use ($request) {
                $q->where('department', $request->input('department'));
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        // Apply sorting
        $sort = $request->input('sort', 'name_asc');
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('first_name', 'desc')->orderBy('last_name', 'desc');
                break;
            case 'created_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'created_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'name_asc':
            default:
                $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
                break;
        }

        $members = $query->with('departments')->paginate(20);

        return view('members.index', compact('members'));
    }

    public function create()
    {
        $roles = Role::active()->get();
        return view('members.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:members,email',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'date_of_birth' => 'nullable|date',
                'baptism_date' => 'nullable|date',
                'departments' => 'required|array|min:1',
                'departments.*' => 'string|in:Media,Choir,Ushers,Dance,Prayer,Lost but Found,Drama,Sanctuary',
                'membership_status' => 'required|string',
                'profile_photo' => 'nullable|image|max:2048',
                'emergency_contacts' => 'nullable|array',
                'custom_fields' => 'nullable|array',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id'
            ]);

            if ($request->hasFile('profile_photo')) {
                // Ensure profile-photos directory exists
                Storage::disk('public')->makeDirectory('profile-photos');
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $validated['profile_photo'] = $path;
            }

            // Remove departments from validated data before creating member
            $departments = $validated['departments'];
            unset($validated['departments']);
            
            $member = Member::create($validated);

            // Create department associations
            foreach ($departments as $department) {
                $member->departments()->create(['department' => $department]);
            }

            if ($request->filled('roles')) {
                $member->roles()->sync($request->input('roles'));
            }

            return redirect()->route('members.show', $member)
                ->with('success', 'Member created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create member: ' . $e->getMessage());
        }
    }

    public function show(Member $member)
    {
        $member->load(['roles', 'attendances', 'donations', 'pledges', 'emergencyContacts', 'documents', 'departments']);
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $member->load('departments');
        $roles = Role::active()->get();
        return view('members.edit', compact('member', 'roles'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('members')->ignore($member->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
            'baptism_date' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'departments' => 'required|array|min:1',
            'departments.*' => 'string|in:Media,Choir,Ushers,Dance,Prayer,Lost but Found,Drama,Sanctuary',
            'membership_status' => 'required|string',
            'profile_photo' => 'nullable|image|max:2048',
            'emergency_contacts' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);

        if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($member->profile_photo) {
                    Storage::disk('public')->delete($member->profile_photo);
                }
                // Ensure profile-photos directory exists
                Storage::disk('public')->makeDirectory('profile-photos');
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $validated['profile_photo'] = $path;
            }

        // Remove departments from validated data before updating member
        $departments = $validated['departments'];
        unset($validated['departments']);

        $member->update($validated);

        // Update department associations
        // First, delete existing departments
        $member->departments()->delete();
        
        // Then create new department associations
        foreach ($departments as $department) {
            $member->departments()->create(['department' => $department]);
        }

        if ($request->filled('roles')) {
            $member->roles()->sync($request->input('roles'));
        }

        return redirect()->route('members.show', $member)
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(Member $member)
    {
        // Delete profile photo if exists
        if ($member->profile_photo) {
            Storage::disk('public')->delete($member->profile_photo);
        }

        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully.');
    }

    public function attendance(Member $member)
    {
        $attendances = $member->attendances()
            ->with('service')
            ->orderByDesc('check_in_time')
            ->paginate(15);

        return view('members.attendance', compact('member', 'attendances'));
    }

    public function donations(Member $member)
    {
        $donations = $member->donations()
            ->orderByDesc('donation_date')
            ->paginate(15);

        return view('members.donations', compact('member', 'donations'));
    }

    public function pledges(Member $member)
    {
        $pledges = $member->pledges()
            ->orderByDesc('pledge_date')
            ->paginate(15);

        return view('members.pledges', compact('member', 'pledges'));
    }
}