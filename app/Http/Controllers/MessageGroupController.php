<?php

namespace App\Http\Controllers;

use App\Models\MessageGroup;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MessageGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:communication.view')->only(['index', 'show', 'members']);
        $this->middleware('permission:communication.create')->only(['create', 'store']);
        $this->middleware('permission:communication.update')->only(['edit', 'update', 'addMembers', 'removeMembers', 'toggle']);
        $this->middleware('permission:communication.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = MessageGroup::withCount('members');

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->orderBy('name')->paginate(15);

        return view('message-groups.index', compact('groups'));
    }

    public function create()
    {
        $members = Member::active()->orderBy('last_name')->get();
        return view('message-groups.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:message_groups',
            'description' => 'nullable|string|max:1000',
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
            'is_active' => 'boolean',
            'auto_add_new_members' => 'boolean',
            'criteria' => 'nullable|array'
        ]);

        DB::beginTransaction();

        try {
            $group = MessageGroup::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'auto_add_new_members' => $validated['auto_add_new_members'] ?? false,
                'criteria' => $validated['criteria'] ?? null,
                'created_by' => Auth::id()
            ]);

            $group->members()->attach($validated['member_ids']);

            DB::commit();

            return redirect()->route('message-groups.show', $group)
                ->with('success', 'Message group created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create message group: ' . $e->getMessage());
        }
    }

    public function show(MessageGroup $group)
    {
        $group->load(['members', 'createdBy']);
        $messageStats = $group->getMessageStatistics();

        return view('message-groups.show', compact('group', 'messageStats'));
    }

    public function edit(MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this group.');
        }

        $members = Member::active()->orderBy('last_name')->get();
        $group->load('members');

        return view('message-groups.edit', compact('group', 'members'));
    }

    public function update(Request $request, MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to update this group.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:message_groups,name,' . $group->id,
            'description' => 'nullable|string|max:1000',
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
            'is_active' => 'boolean',
            'auto_add_new_members' => 'boolean',
            'criteria' => 'nullable|array'
        ]);

        DB::beginTransaction();

        try {
            $group->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'is_active' => $validated['is_active'] ?? true,
                'auto_add_new_members' => $validated['auto_add_new_members'] ?? false,
                'criteria' => $validated['criteria'] ?? null
            ]);

            $group->members()->sync($validated['member_ids']);

            DB::commit();

            return redirect()->route('message-groups.show', $group)
                ->with('success', 'Message group updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update message group: ' . $e->getMessage());
        }
    }

    public function destroy(MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to delete this group.');
        }

        if ($group->messages()->exists()) {
            return redirect()->route('message-groups.index')
                ->with('error', 'Cannot delete group that has associated messages.');
        }

        $group->delete();

        return redirect()->route('message-groups.index')
            ->with('success', 'Message group deleted successfully.');
    }

    public function members(MessageGroup $group)
    {
        return response()->json([
            'members' => $group->members()->select('id', 'first_name', 'last_name', 'email')->get()
        ]);
    }

    public function addMembers(Request $request, MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to modify this group.');
        }

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id'
        ]);

        $group->members()->attach($validated['member_ids']);

        return redirect()->route('message-groups.show', $group)
            ->with('success', 'Members added successfully.');
    }

    public function removeMembers(Request $request, MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to modify this group.');
        }

        $validated = $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id'
        ]);

        $group->members()->detach($validated['member_ids']);

        return redirect()->route('message-groups.show', $group)
            ->with('success', 'Members removed successfully.');
    }

    public function toggle(MessageGroup $group)
    {
        if (!$group->canBeModifiedBy(Auth::user())) {
            abort(403, 'You do not have permission to modify this group.');
        }

        $group->update(['is_active' => !$group->is_active]);

        $status = $group->is_active ? 'activated' : 'deactivated';
        return redirect()->route('message-groups.show', $group)
            ->with('success', "Message group {$status} successfully.");
    }

    public function applyAutoAddCriteria()
    {
        $groups = MessageGroup::where('auto_add_new_members', true)
            ->whereNotNull('criteria')
            ->get();

        $addedCount = 0;

        foreach ($groups as $group) {
            $addedCount += $group->applyAutoAddCriteria();
        }

        return redirect()->route('message-groups.index')
            ->with('success', "{$addedCount} members auto-added to groups based on criteria.");
    }
}