<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberDepartment;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->input('member_type'));
        }

        if ($request->filled('department')) {
            $query->whereHas('departments', function ($q) use ($request) {
                // Now filtering by department name via the relation
                $q->whereHas('department', function ($subQ) use ($request) {
                    $subQ->where('name', $request->input('department'));
                });
            });
        }

        if ($request->filled('church_group')) {
            $query->where('church_group', $request->input('church_group'));
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

        $members = $query->with('departments.department')->paginate(20);
        
        $formDepartments = ['CHOIR', 'EVANGELISM', 'USHERING', 'DECORATION', 'INTERPRETATION', 'SUNDAY SCHOOL', 'DOCUMENTATION', 'DRAMA', 'SECURITY', 'MEDIA', 'PROTOCOL', 'SANCTUARY KEEPER', 'TECHNICAL', 'PRAYER', 'NONE'];
        $formGroups = ['The Levites', 'The Light bearers', 'The Root of Jesse', 'Ark of Covenant', 'God\'s Workmanship', 'Glorious star', 'Bread of Life', 'Wisdom of God', 'The Gospellers', 'Balm of Gilead', 'New creature', 'Heaven Ambassadors', 'Battle axe', 'PEACE FELLOWSHIP', 'REDEEMED', 'Light of the World', 'THE LORD CHOSEN', 'Salt of the World', 'Daughters of Zion'];

        return view('members.index', compact('members', 'formDepartments', 'formGroups'));
    }

    public function create()
    {
        $roles = Role::active()->get();
        $departments = \App\Models\Department::where('is_active', true)->get();
        return view('members.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'other_names' => 'nullable|string|max:255',
                'email' => 'required|email|unique:members,email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string|max:500',
                'birth_day' => 'required|string|max:2',
                'birth_month' => 'required|string|max:20',
                'gender' => 'required|string|in:male,female,other,MALE,FEMALE,OTHER',
                'marital_status' => 'required|string',
                'partner_name' => 'nullable|string|max:255',
                'state_of_origin' => 'required|string|max:255',
                'lga_of_origin' => 'required|string|max:255',
                'state_of_residence' => 'required|string|max:255',
                'city_of_residence' => 'required|string|max:255',
                'profession' => 'required|string|max:255',
                'church_group' => 'nullable|string|max:255',
                'department' => 'required|string|max:255',
                'is_baptized' => 'required|string',
                'baptism_year_and_place' => 'nullable|string|max:255',
                'baptism_church_name' => 'nullable|string|max:255',
                'spiritual_gifts' => 'nullable|string',
                'emergency_contact_details' => 'nullable|string',
                
                'membership_status' => 'nullable|string',
                'profile_photo' => 'nullable|image|max:2048',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id'
            ]);

            if ($request->hasFile('profile_photo')) {
                // Ensure profile-photos directory exists
                Storage::disk('public')->makeDirectory('profile-photos');
                $path = $request->file('profile_photo')->store('profile-photos', 'public');
                $validated['profile_photo'] = $path;
            }
            
            $departmentName = $validated['department'];
            unset($validated['department']);
            
            $validated['gender'] = strtolower($validated['gender']);
            $validated['membership_status'] = $validated['membership_status'] ?? 'active';
            
            DB::beginTransaction();
            $member = Member::create($validated);

            // Find or create department by name
            $department = \App\Models\Department::firstOrCreate(
                ['name' => $departmentName],
                ['is_active' => true, 'description' => $departmentName . ' Department']
            );
            $member->departments()->create(['department_id' => $department->id]);

            if ($request->filled('roles')) {
                $member->roles()->sync($request->input('roles'));
            }

            DB::commit();

            return redirect()->route('members.show', $member)
                ->with('success', 'Member created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create member: ' . $e->getMessage());
        }
    }

    public function show(Member $member)
    {
        $member->load(['roles', 'attendances', 'donations', 'pledges', 'emergencyContacts', 'documents', 'departments.department']);
        return view('members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $member->load('departments');
        $roles = Role::active()->get();
        $departments = \App\Models\Department::where('is_active', true)->get();
        return view('members.edit', compact('member', 'roles', 'departments'));
    }

    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('members')->ignore($member->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'birth_day' => 'required|string|max:2',
            'birth_month' => 'required|string|max:20',
            'gender' => 'required|string|in:male,female,other,MALE,FEMALE,OTHER',
            'marital_status' => 'required|string',
            'partner_name' => 'nullable|string|max:255',
            'state_of_origin' => 'required|string|max:255',
            'lga_of_origin' => 'required|string|max:255',
            'state_of_residence' => 'required|string|max:255',
            'city_of_residence' => 'required|string|max:255',
            'profession' => 'required|string|max:255',
            'church_group' => 'nullable|string|max:255',
            'department' => 'required|string|max:255',
            'is_baptized' => 'required|string',
            'baptism_year_and_place' => 'nullable|string|max:255',
            'baptism_church_name' => 'nullable|string|max:255',
            'spiritual_gifts' => 'nullable|string',
            'emergency_contact_details' => 'nullable|string',
            
            'membership_status' => 'required|string',
            'profile_photo' => 'nullable|image|max:2048',
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

        $departmentName = $validated['department'];
        unset($validated['department']);
        
        $validated['gender'] = strtolower($validated['gender']);

        $member->update($validated);

        // Update department associations
        $member->departments()->delete();
        $department = \App\Models\Department::firstOrCreate(
            ['name' => $departmentName],
            ['is_active' => true, 'description' => $departmentName . ' Department']
        );
        $member->departments()->create(['department_id' => $department->id]);

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

    public function promote(Member $member)
    {
        $member->update(['member_type' => 'main_member']);

        return redirect()->back()->with('success', 'Member promoted to Main Member successfully.');
    }
}