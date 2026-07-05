<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Pledge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PledgeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:finance.view')->only(['index', 'show', 'report']);
        // $this->middleware('permission:finance.create')->only(['create', 'store']);
        // $this->middleware('permission:finance.update')->only(['edit', 'update', 'recordPayment']);
        // $this->middleware('permission:finance.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Pledge::with('member');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('campaign_name')) {
            $query->where('campaign_name', 'like', '%'.$request->input('campaign_name').'%');
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->input('end_date'));
        }

        $pledges = $query->orderByDesc('start_date')->paginate(15);
        $members = Member::orderBy('first_name')->get();

        return view('pledges.index', compact('pledges', 'members'));
    }

    public function create()
    {
        $members = Member::orderBy('first_name')->get();

        return view('pledges.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'campaign_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:active,completed,defaulted',
            'notes' => 'nullable|string',
        ]);

        $pledge = Pledge::create($validated);

        return redirect()->route('pledges.show', $pledge)
            ->with('success', 'Pledge created successfully.');
    }

    public function show(Pledge $pledge)
    {
        $pledge->load(['member']);

        return view('pledges.show', compact('pledge'));
    }

    public function edit(Pledge $pledge)
    {
        $members = Member::orderBy('first_name')->get();

        return view('pledges.edit', compact('pledge', 'members'));
    }

    public function update(Request $request, Pledge $pledge)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'campaign_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0',
            'amount_paid' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:active,completed,defaulted',
            'notes' => 'nullable|string',
        ]);

        $pledge->update($validated);

        return redirect()->route('pledges.show', $pledge)
            ->with('success', 'Pledge updated successfully.');
    }

    public function destroy(Pledge $pledge)
    {
        $pledge->delete();

        return redirect()->route('pledges.index')
            ->with('success', 'Pledge deleted successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfYear()->format('Y-m-d'));
        $status = $request->input('status');

        // Base query
        $query = Pledge::with('member')
            ->whereBetween('start_date', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        // Overall statistics
        $totalPledges = $query->count();
        $totalPledgeAmount = $query->sum('total_amount');
        $totalPaidAmount = $query->sum('amount_paid');
        $totalOutstanding = $totalPledgeAmount - $totalPaidAmount;
        $fulfillmentRate = $totalPledgeAmount > 0 ? ($totalPaidAmount / $totalPledgeAmount) * 100 : 0;

        // Status breakdown
        $statusBreakdown = Pledge::whereBetween('start_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('SUM(amount_paid) as amount_paid'))
            ->groupBy('status')
            ->get();

        // Campaign breakdown
        $campaignBreakdown = Pledge::whereBetween('start_date', [$startDate, $endDate])
            ->select('campaign_name', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'), DB::raw('SUM(amount_paid) as amount_paid'))
            ->groupBy('campaign_name')
            ->orderByDesc('total_amount')
            ->get();

        // Monthly trends
        $monthlyTrends = Pledge::whereBetween('start_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(amount_paid) as amount_paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top pledgers
        $topPledgers = Pledge::with('member')
            ->whereBetween('start_date', [$startDate, $endDate])
            ->select('member_id', DB::raw('SUM(total_amount) as total_pledged'), DB::raw('SUM(amount_paid) as total_paid'))
            ->groupBy('member_id')
            ->orderByDesc('total_pledged')
            ->limit(10)
            ->get();

        // Overdue pledges
        $overduePledges = Pledge::with('member')
            ->where('end_date', '<', now())
            ->where('status', 'active')
            ->whereRaw('amount_paid < total_amount')
            ->get();

        return view('pledges.report', compact(
            'startDate',
            'endDate',
            'status',
            'totalPledges',
            'totalPledgeAmount',
            'totalPaidAmount',
            'totalOutstanding',
            'fulfillmentRate',
            'statusBreakdown',
            'campaignBreakdown',
            'monthlyTrends',
            'topPledgers',
            'overduePledges'
        ));
    }

    /**
     * API: Get pledges for the authenticated user
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        $pledges = Pledge::where('member_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pledges
        ]);
    }

    /**
     * API: Store a new pledge
     */
    public function apiStore(Request $request)
    {
        $request->validate([
            'campaign_name' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $member = $user->member;

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'User profile is not linked to a member record. Please update your profile first.'
            ], 400);
        }

        $pledge = Pledge::create([
            'church_id' => $user->church_id ?? null,
            'member_id' => $member->id,
            'campaign_name' => $request->campaign_name,
            'total_amount' => $request->total_amount,
            'amount_paid' => 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pledge submitted successfully',
            'data' => $pledge
        ], 201);
    }
}