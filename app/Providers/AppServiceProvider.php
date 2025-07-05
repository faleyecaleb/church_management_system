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
        // Skip symlink creation on shared hosting where exec() is disabled
        if (!is_dir(public_path('storage')) && !$this->isSharedHosting()) {
            try {
                app('files')->link(
                    storage_path('app/public'),
                    public_path('storage')
                );
            } catch (\Exception $e) {
                // Silently fail on shared hosting - manual symlink creation required
                \Log::warning('Storage symlink creation failed: ' . $e->getMessage());
            }
        }

        // Ensure profile-photos directory exists
        if (!Storage::disk('public')->exists('profile-photos')) {
            Storage::disk('public')->makeDirectory('profile-photos');
        }
    }

    /**
     * Check if running on shared hosting where exec() might be disabled
     */
    private function isSharedHosting(): bool
    {
        return !function_exists('exec') || !function_exists('symlink');
    }
}
