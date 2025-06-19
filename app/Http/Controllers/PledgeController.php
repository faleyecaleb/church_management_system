<?php

namespace App\Http\Controllers;

use App\Models\Pledge;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PledgeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:finance.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:finance.create')->only(['create', 'store']);
        $this->middleware('permission:finance.update')->only(['edit', 'update', 'recordPayment']);
        $this->middleware('permission:finance.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Pledge::with('member');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('campaign')) {
            $query->where('campaign', $request->input('campaign'));
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        if ($request->filled('start_date')) {
            $query->where('pledge_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('pledge_date', '<=', $request->input('end_date'));
        }

        $pledges = $query->orderByDesc('pledge_date')->paginate(15);
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
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'pledge_date' => 'required|date',
            'target_date' => 'required|date|after:pledge_date',
            'campaign' => 'required|string',
            'payment_frequency' => 'required|string',
            'status' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $pledge = Pledge::create($validated);

        return redirect()->route('pledges.show', $pledge)
            ->with('success', 'Pledge created successfully.');
    }

    public function show(Pledge $pledge)
    {
        $pledge->load(['member', 'payments']);
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
            'amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'pledge_date' => 'required|date',
            'target_date' => 'required|date|after:pledge_date',
            'campaign' => 'required|string',
            'payment_frequency' => 'required|string',
            'status' => 'required|string',
            'notes' => 'nullable|string'
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

    public function recordPayment(Request $request, Pledge $pledge)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $pledge->recordPayment(
            $validated['amount'],
            $validated['payment_date'],
            $validated['payment_method'],
            $validated['notes'] ?? null
        );

        return redirect()->route('pledges.show', $pledge)
            ->with('success', 'Payment recorded successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear());
        $endDate = $request->input('end_date', now());

        // Pledges by status
        $byStatus = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(paid_amount) as total_paid'))
            ->groupBy('status')
            ->get();

        // Pledges by campaign
        $byCampaign = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->select('campaign', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'), DB::raw('SUM(paid_amount) as total_paid'))
            ->groupBy('campaign')
            ->get();

        // Monthly pledges
        $monthlyPledges = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(pledge_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(paid_amount) as total_paid')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top pledgers
        $topPledgers = Pledge::whereBetween('pledge_date', [$startDate, $endDate])
            ->with('member:id,first_name,last_name')
            ->select('member_id', DB::raw('SUM(amount) as total_pledged'), DB::raw('SUM(paid_amount) as total_paid'))
            ->groupBy('member_id')
            ->orderByDesc('total_pledged')
            ->limit(10)
            ->get();

        // Overall statistics
        $overallStats = [
            'total_pledges' => Pledge::whereBetween('pledge_date', [$startDate, $endDate])->count(),
            'total_amount' => Pledge::whereBetween('pledge_date', [$startDate, $endDate])->sum('amount'),
            'total_paid' => Pledge::whereBetween('pledge_date', [$startDate, $endDate])->sum('paid_amount'),
            'completion_rate' => Pledge::whereBetween('pledge_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->count() / max(1, Pledge::whereBetween('pledge_date', [$startDate, $endDate])->count()) * 100
        ];

        return view('pledges.report', compact(
            'byStatus',
            'byCampaign',
            'monthlyPledges',
            'topPledgers',
            'overallStats',
            'startDate',
            'endDate'
        ));
    }
}