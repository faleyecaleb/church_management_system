<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ThemeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $theme = 'default';

        if (Auth::check()) {
            $user = Auth::user();
            $churchId = $user->church_id;

            // If it's a super admin, they might have a selected branch in session,
            // but the system seems to set the user's church_id temporarily when they switch.
            // Let's rely on the church_id.

            if ($churchId) {
                // Determine theme based on church name or type.
                // Assuming IDs 1=Adult, 2=Youth, 3=Children based on seeder.
                // It's safer to fetch the church, but we can do a quick check.
                $church = \App\Models\Church::find($churchId);
                
                if ($church) {
                    if (stripos($church->name, 'Adult') !== false) {
                        $theme = 'adult';
                    } elseif (stripos($church->name, 'Youth') !== false) {
                        $theme = 'youth';
                    } elseif (stripos($church->name, 'Children') !== false) {
                        $theme = 'children';
                    }
                }
            }
        }

        // Share the theme variable globally to all views
        View::share('activeTheme', $theme);

        return $next($request);
    }
}
