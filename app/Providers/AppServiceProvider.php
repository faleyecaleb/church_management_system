<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure storage disk is properly configured
        if (!is_dir(public_path('storage'))) {
            app('files')->link(
                storage_path('app/public'),
                public_path('storage')
            );
        }

        // Ensure profile-photos directory exists
        if (!Storage::disk('public')->exists('profile-photos')) {
            Storage::disk('public')->makeDirectory('profile-photos');
        }
    }
}
