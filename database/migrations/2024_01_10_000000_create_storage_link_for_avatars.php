<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

class CreateStorageLinkForAvatars extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the symbolic link for public storage
        Artisan::call('storage:link');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the symbolic link
        $storagePath = public_path('storage');
        if (is_link($storagePath)) {
            unlink($storagePath);
        } elseif (is_dir($storagePath)) {
            rmdir($storagePath);
        }
    }
}