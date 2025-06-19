<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:finance.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:finance.create')->only(['create', 'store']);
        $this->middleware('permission:finance.update')->only(['edit', 'update']);
        $this->middleware('permission:finance.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = Donation::with('member');

        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('donation_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->where('donation_date', '<=', $request->input('end_date'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        if ($request->filled('campaign')) {
            $query->where('campaign', $request->input('campaign'));
        }

        if ($request->filled('member_id')) {
            $query->where('member_id', $request->input('member_id'));
        }

        $donations = $query->orderByDesc('donation_date')->paginate(15);
        $members = Member::orderBy('first_name')->get();

        return view('donations.index', compact('donations', 'members'));
    }

    public function create()
    {
        $members = Member::orderBy('first_name')->get();
        return view('donations.create', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0',
            'donation_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'campaign' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'required_if:is_recurring,true|string',
            'next_payment_date' => 'required_if:is_recurring,true|date|after:donation_date'
        ]);

        $donation = Donation::create($validated);

        // Send receipt if email is available
        if ($donation->member->email) {
            // TODO: Implement donation receipt email
        }

        return redirect()->route('donations.show', $donation)
            ->with('success', 'Donation recorded successfully.');
    }

    public function show(Donation $donation)
    {
        $donation->load('member');
        return view('donations.show', compact('donation'));
    }

    public function edit(Donation $donation)
    {
        $members = Member::orderBy('first_name')->get();
        return view('donations.edit', compact('donation', 'members'));
    }

    public function update(Request $request, Donation $donation)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0',
            'donation_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'campaign' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'required_if:is_recurring,true|string',
            'next_payment_date' => 'required_if:is_recurring,true|date|after:donation_date'
        ]);

        $donation->update($validated);

        return redirect()->route('donations.show', $donation)
            ->with('success', 'Donation updated successfully.');
    }

    public function destroy(Donation $donation)
    {
        $donation->delete();

        return redirect()->route('donations.index')
            ->with('success', 'Donation deleted successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfYear());
        $endDate = $request->input('end_date', now());

        // Total donations by payment method
        $byPaymentMethod = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Total donations by campaign
        $byCampaign = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select('campaign', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('campaign')
            ->get();

        // Monthly totals
        $monthlyTotals = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(donation_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top donors
        $topDonors = Donation::whereBetween('donation_date', [$startDate, $endDate])
            ->with('member:id,first_name,last_name')
            ->select('member_id', DB::raw('SUM(amount) as total'))
            ->groupBy('member_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Recurring donations stats
        $recurringStats = [
            'total_recurring' => Donation::where('is_recurring', true)->count(),
            'active_recurring' => Donation::where('is_recurring', true)
                ->where('next_payment_date', '>=', now())
                ->count(),
            'total_amount' => Donation::where('is_recurring', true)
                ->sum('amount')
        ];

        return view('donations.report', compact(
            'byPaymentMethod',
            'byCampaign',
            'monthlyTotals',
            'topDonors',
            'recurringStats',
            'startDate',
            'endDate'
        ));
    }

    public function generateReceipt(Donation $donation)
    {
        // TODO: Implement receipt generation logic
        return view('donations.receipt', compact('donation'));
    }
}