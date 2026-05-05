<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ChurchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // If no user is authenticated, we might be in console/artisan or a public route.
        if (Auth::hasUser()) {
            $user = Auth::user();

            // If the user has a specific church_id selected (either they are a regular admin or a super_admin who switched branch)
            if ($user->church_id) {
                $builder->where($model->getTable() . '.church_id', $user->church_id);
            } else {
                // If the user has NO church_id, they MUST be a super_admin to see everything.
                // If they are not a super admin, they should see nothing (fail-safe).
                if (!$user->isSuperAdmin()) {
                    $builder->whereRaw('1 = 0');
                }
            }
        }
    }
}
