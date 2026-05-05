<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchController extends Controller
{
    /**
     * Switch the active church branch for the Super Admin.
     */
    public function switch(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $churchId = $request->input('church_id');
        
        // Update user's church context
        $user->church_id = $churchId ?: null;
        $user->save();

        $branchName = $churchId ? \App\Models\Church::find($churchId)->name : 'All Branches';
        
        return back()->with('success', "Switched to {$branchName} successfully.");
    }
}
