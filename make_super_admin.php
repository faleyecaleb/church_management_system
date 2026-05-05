<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::first();
if ($user) {
    $user->role = 'super_admin';
    $user->save();
    echo "Made user {$user->email} a super_admin!\n";
} else {
    echo "No users found.\n";
}
