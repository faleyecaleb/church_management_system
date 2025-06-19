<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (!Auth::user()->isAdmin()) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => ['Only administrators can access this system.'],
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $data = [
            'totalMembers' => \App\Models\Member::count(),
            'totalPledges' => \App\Models\Pledge::sum('amount_paid'),
            'totalExpenses' => \App\Models\Expense::sum('amount'),
            'totalPrayerRequests' => \App\Models\PrayerRequest::count(),
            'recentActivities' => \App\Models\AuditLog::with('user')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($log) {
                    $log->type_color = $this->getActivityTypeColor($log->event);
                    $log->icon = $this->getActivityIcon($log->event);
                    return $log;
                }),
            'chartData' => $this->getFinancialChartData(),
        ];

        return view('admin.dashboard', $data);
    }

    /**
     * Get the color class for different activity types.
     *
     * @param string $type
     * @return string
     */
    private function getActivityTypeColor($type)
    {
        return match ($type) {
            'created' => 'bg-green-500',
            'updated' => 'bg-blue-500',
            'deleted' => 'bg-red-500',
            default => 'bg-gray-500',
        };
    }

    /**
     * Get the icon for different activity types.
     *
     * @param string $type
     * @return string
     */
    private function getActivityIcon($type)
    {
        return match ($type) {
            'created' => 'fa-plus',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            default => 'fa-info',
        };
    }

    /**
     * Get financial data for the chart.
     *
     * @return array
     */
    private function getFinancialChartData()
    {
        $months = collect(range(5, 0))->map(function ($month) {
            return now()->subMonths($month)->format('M Y');
        });

        $pledges = collect(range(5, 0))->map(function ($month) {
            return \App\Models\Pledge::whereYear('created_at', now()->subMonths($month)->year)
                ->whereMonth('created_at', now()->subMonths($month)->month)
                ->sum('amount_paid');
        });

        $expenses = collect(range(5, 0))->map(function ($month) {
            return \App\Models\Expense::whereYear('created_at', now()->subMonths($month)->year)
                ->whereMonth('created_at', now()->subMonths($month)->month)
                ->sum('amount');
        });

        return [
            'labels' => $months,
            'pledges' => $pledges,
            'expenses' => $expenses,
        ];
    }
}