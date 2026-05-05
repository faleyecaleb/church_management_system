<?php

namespace App\Traits;

use App\Models\Church;
use App\Models\Scopes\ChurchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToChurch
{
    /**
     * Boot the BelongsToChurch trait for a model.
     */
    protected static function bootBelongsToChurch(): void
    {
        static::addGlobalScope(new ChurchScope);

        static::creating(function ($model) {
            if (auth()->check() && ! $model->church_id) {
                // If the user is super admin, we shouldn't automatically assign their church_id
                // unless they explicitly selected a church context.
                // For now, if the auth user has a church_id, assign it.
                if (auth()->user()->church_id) {
                    $model->church_id = auth()->user()->church_id;
                }
            }
        });
    }

    /**
     * Get the church that owns the model.
     */
    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }
}
