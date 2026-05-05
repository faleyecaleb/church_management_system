<?php

$models = [
    'Member', 'Service', 'Attendance', 'Pledge', 'Expense', 'Budget', 'Donation',
    'Message', 'PrayerRequest', 'Complaint', 'Equipment', 'OrderOfService',
    'InternalMessage', 'MessageGroup', 'Notification'
];

$dir = __DIR__ . '/app/Models/';

foreach ($models as $modelName) {
    $filePath = $dir . $modelName . '.php';
    if (file_exists($filePath)) {
        $content = file_get_contents($filePath);
        
        // Ensure namespace App\Traits\BelongsToChurch; is imported
        if (strpos($content, 'use App\Traits\BelongsToChurch;') === false) {
            // Insert after namespace or other imports
            $content = preg_replace('/namespace App\\\\Models;/', "namespace App\\Models;\n\nuse App\\Traits\\BelongsToChurch;", $content, 1);
        }
        
        // Ensure trait is used inside the class
        if (strpos($content, 'use BelongsToChurch;') === false) {
            // Find class declaration and insert inside
            $pattern = '/class ' . $modelName . ' extends [^{]+\s*\{/';
            $replacement = "$0\n    use BelongsToChurch;\n";
            $content = preg_replace($pattern, $replacement, $content, 1);
        }
        
        file_put_contents($filePath, $content);
        echo "Updated $modelName\n";
    } else {
        echo "File not found: $modelName\n";
    }
}
