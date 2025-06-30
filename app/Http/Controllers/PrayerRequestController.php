<?php

namespace App\Http\Controllers;

use App\Models\PrayerRequest;
use App\Models\Member;
use App\Models\Prayer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PrayerRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:prayer.view')->only(['index', 'show', 'report']);
        $this->middleware('permission:prayer.create')->only(['create', 'store']);
        $this->middleware('permission:prayer.update')->only(['edit', 'update', 'recordPrayer', 'markAsCompleted', 'archive', 'reactivate']);
        $this->middleware('permission:prayer.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = PrayerRequest::with(['requestor', 'prayers'])
            ->when(!Auth::user()->hasPermission('prayer.view_all'), function ($q) {
                $q->where(function ($query) {
                    $query->where('requestor_id', Auth::id())
                        ->orWhere('is_public', true);
                });
            });

        // Apply filters
        if ($request->filled('status')) {
            switch ($request->input('status')) {
                case 'active':
                    $query->active();
                    break;
                case 'completed':
                    $query->completed();
                    break;
                case 'archived':
                    $query->archived();
                    break;
            }
        } else {
            $query->active(); // Default to active requests
        }

        if ($request->filled('privacy')) {
            $query->where('is_public', $request->input('privacy') === 'public');
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('needs_prayer')) {
            $query->needsPrayer();
        }

        $requests = $query->orderByDesc('created_at')->paginate(15);

        return view('prayer-requests.index', compact('requests'));
    }

    public function create()
    {
        return view('prayer-requests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_public' => 'boolean',
            'prayer_target' => 'nullable|integer|min:1',
            'prayer_frequency' => 'nullable|integer|min:1',
            'end_date' => 'nullable|date|after:today'
        ]);

        $prayerRequest = PrayerRequest::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_public' => $validated['is_public'] ?? false,
            'requestor_id' => Auth::id(),
            'prayer_target' => $validated['prayer_target'],
            'prayer_frequency' => $validated['prayer_frequency'],
            'end_date' => $validated['end_date'],
            'status' => 'active'
        ]);

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer request created successfully.');
    }

    public function show(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to view this prayer request.');
        }

        $prayerRequest->load(['requestor', 'prayers.user', 'prayers.member']);
        $stats = $prayerRequest->getPrayerStats();

        return view('prayer-requests.show', compact('prayerRequest', 'stats'));
    }

    public function edit(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this prayer request.');
        }

        return view('prayer-requests.edit', compact('prayerRequest'));
    }

    public function update(Request $request, PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to update this prayer request.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'is_public' => 'boolean',
            'prayer_target' => 'nullable|integer|min:1',
            'prayer_frequency' => 'nullable|integer|min:1',
            'end_date' => 'nullable|date|after:today'
        ]);

        $prayerRequest->update($validated);

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer request updated successfully.');
    }

    public function destroy(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to delete this prayer request.');
        }

        $prayerRequest->delete();

        return redirect()->route('prayer-requests.index')
            ->with('success', 'Prayer request deleted successfully.');
    }

    public function pray(Request $request, PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to record prayer for this request.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        // Record the prayer using the model method
        $prayerRequest->recordPrayer(Auth::id(), $validated['notes'] ?? null);

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Thank you for your prayer! It has been recorded.');
    }

    public function recordPrayer(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to record prayer for this request.');
        }

        $prayer = $prayerRequest->recordPrayer(Auth::id());

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer recorded successfully.');
    }

    public function markAsCompleted(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to mark this prayer request as completed.');
        }

        $prayerRequest->markAsCompleted();

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer request marked as completed.');
    }

    public function archive(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to archive this prayer request.');
        }

        $prayerRequest->archive();

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer request archived successfully.');
    }

    public function reactivate(PrayerRequest $prayerRequest)
    {
        if (!$prayerRequest->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to reactivate this prayer request.');
        }

        $prayerRequest->reactivate();

        return redirect()->route('prayer-requests.show', $prayerRequest)
            ->with('success', 'Prayer request reactivated successfully.');
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        // Requests by status
        $byStatus = PrayerRequest::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Daily prayer counts
        $dailyPrayers = Prayer::whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top prayer warriors (users)
        $topUserPrayerWarriors = Prayer::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('user_id')
            ->with('user')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Top prayer warriors (members)
        $topMemberPrayerWarriors = Prayer::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('member_id')
            ->with('member')
            ->select('member_id', DB::raw('COUNT(*) as count'))
            ->groupBy('member_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Most prayed for requests
        $mostPrayedRequests = PrayerRequest::withCount(['prayers' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
            ->having('prayers_count', '>', 0)
            ->orderByDesc('prayers_count')
            ->limit(10)
            ->get();

        return view('prayer-requests.report', compact(
            'byStatus',
            'dailyPrayers',
            'topUserPrayerWarriors',
            'topMemberPrayerWarriors',
            'mostPrayedRequests',
            'startDate',
            'endDate'
        ));
    }
}