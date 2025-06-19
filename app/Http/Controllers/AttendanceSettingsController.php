<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class AttendanceSettingsController extends Controller
{
    /**
     * Show the attendance settings form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('attendance.settings');
    }

    /**
     * Update the attendance settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'qr_expiry_before' => 'required|integer|min:0|max:120',
            'qr_expiry_after' => 'required|integer|min:0|max:120',
            'enable_mobile_checkin' => 'boolean',
            'require_geofencing' => 'boolean',
            'allowed_distance' => 'required|integer|min:10|max:1000',
            'church_latitude' => 'required_if:require_geofencing,true|numeric|between:-90,90',
            'church_longitude' => 'required_if:require_geofencing,true|numeric|between:-180,180',
        ]);

        // Update the .env file with new values
        $this->updateEnvironmentFile([
            'QR_EXPIRY_BEFORE' => $validated['qr_expiry_before'],
            'QR_EXPIRY_AFTER' => $validated['qr_expiry_after'],
            'ENABLE_MOBILE_CHECKIN' => $validated['enable_mobile_checkin'] ? 'true' : 'false',
            'REQUIRE_GEOFENCING' => $validated['require_geofencing'] ? 'true' : 'false',
            'ALLOWED_DISTANCE' => $validated['allowed_distance'],
            'CHURCH_LATITUDE' => $validated['church_latitude'],
            'CHURCH_LONGITUDE' => $validated['church_longitude'],
        ]);

        return redirect()->route('attendance.settings.index')
            ->with('success', 'Attendance settings updated successfully.');
    }

    /**
     * Update the environment file with new values.
     *
     * @param  array  $values
     * @return void
     */
    protected function updateEnvironmentFile($values)
    {
        $envFile = app()->environmentFilePath();
        $contentArray = file($envFile, FILE_IGNORE_NEW_LINES);

        foreach ($values as $key => $value) {
            $updated = false;

            // Update existing variables
            foreach ($contentArray as $lineNum => $line) {
                if (strpos($line, $key . '=') === 0) {
                    $contentArray[$lineNum] = $key . '=' . $value;
                    $updated = true;
                    break;
                }
            }

            // Add new variables
            if (!$updated) {
                $contentArray[] = $key . '=' . $value;
            }
        }

        // Write the updated content back to the .env file
        file_put_contents($envFile, implode("\n", $contentArray));

        // Clear the config cache
        if (function_exists('exec')) {
            exec('php artisan config:clear');
        }
    }
}