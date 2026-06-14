<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        if (!Auth::user()->hasPermission($permission)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized. Permission required: ' . $permission], 403);
            }
            
            return redirect()->route('admin.dashboard')->with('error', 'Unauthorized. Permission required: ' . $permission);
        }

        return $next($request);
    }
}