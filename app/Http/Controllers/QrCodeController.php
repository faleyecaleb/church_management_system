<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    /**
     * Generate a new QR code for a service.
     *
     * @param  \App\Models\Service  $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Service $service)
    {
        $token = $this->generateToken($service);
        $url = route('attendance.check-in', ['token' => $token]);
        
        $qrCode = QrCode::size(300)
            ->format('svg')
            ->errorCorrection('H')
            ->style('round')
            ->eye('circle')
            ->margin(1)
            ->generate($url);

        return response()->json([
            'qr_code' => $qrCode,
            'token' => $token,
            'expires_at' => $this->getExpiryTime($service),
        ]);
    }

    /**
     * Process check-in via QR code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIn(Request $request)
    {
        try {
            $token = $request->input('token');
            $serviceId = $this->validateToken($token);
            
            if (!$serviceId) {
                return response()->json(['error' => 'Invalid or expired QR code'], 400);
            }

            $service = Service::findOrFail($serviceId);

            // Verify if check-in is within the allowed time window
            if (!$this->isWithinCheckInWindow($service)) {
                return response()->json(['error' => 'Check-in is not available at this time'], 400);
            }

            // Verify location if geofencing is enabled
            if (config('attendance.require_geofencing') && !$this->verifyLocation($request)) {
                return response()->json(['error' => 'You are not within the allowed check-in area'], 400);
            }

            // Create attendance record
            $attendance = Attendance::create([
                'service_id' => $service->id,
                'member_id' => auth()->user()->id,
                'check_in_time' => now(),
                'check_in_method' => 'qr_code',
                'location_verified' => config('attendance.require_geofencing'),
            ]);

            return response()->json([
                'message' => 'Check-in successful',
                'attendance' => $attendance,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Check-in failed'], 500);
        }
    }

    /**
     * Generate a secure token for the service.
     *
     * @param  \App\Models\Service  $service
     * @return string
     */
    protected function generateToken(Service $service)
    {
        $random = Str::random(config('attendance.token_length', 32));
        $data = $service->id . '|' . $random . '|' . $this->getExpiryTime($service)->timestamp;
        
        return base64_encode(Hash::make($data, [
            'rounds' => 5,
        ]));
    }

    /**
     * Validate and decode a check-in token.
     *
     * @param  string  $token
     * @return int|null
     */
    protected function validateToken($token)
    {
        try {
            $data = base64_decode($token);
            if (!$data) return null;

            list($serviceId, $random, $expiry) = explode('|', $data);
            
            if (now()->timestamp > $expiry) {
                return null;
            }

            return (int) $serviceId;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate the expiry time for a service's QR code.
     *
     * @param  \App\Models\Service  $service
     * @return \Carbon\Carbon
     */
    protected function getExpiryTime(Service $service)
    {
        $startTime = $service->start_time;
        $expiryAfter = config('attendance.qr_expiry_after', 15);
        
        return $startTime->addMinutes($expiryAfter);
    }

    /**
     * Check if the current time is within the allowed check-in window.
     *
     * @param  \App\Models\Service  $service
     * @return bool
     */
    protected function isWithinCheckInWindow(Service $service)
    {
        $now = now();
        $windowStart = $service->start_time->subMinutes(config('attendance.qr_expiry_before', 15));
        $windowEnd = $service->start_time->addMinutes(config('attendance.qr_expiry_after', 15));

        return $now->between($windowStart, $windowEnd);
    }

    /**
     * Verify if the user's location is within the allowed distance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifyLocation(Request $request)
    {
        $userLat = $request->input('latitude');
        $userLng = $request->input('longitude');
        
        if (!$userLat || !$userLng) {
            return false;
        }

        $churchLat = config('attendance.church_latitude');
        $churchLng = config('attendance.church_longitude');
        $maxDistance = config('attendance.allowed_distance', 100);

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $userLat,
            $userLng,
            $churchLat,
            $churchLng
        );

        return $distance <= $maxDistance;
    }

    /**
     * Calculate distance between two points using Haversine formula.
     *
     * @param  float  $lat1
     * @param  float  $lng1
     * @param  float  $lat2
     * @param  float  $lng2
     * @return float Distance in meters
     */
    protected function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $lat1 = deg2rad($lat1);
        $lng1 = deg2rad($lng1);
        $lat2 = deg2rad($lat2);
        $lng2 = deg2rad($lng2);

        $latDelta = $lat2 - $lat1;
        $lngDelta = $lng2 - $lng1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lngDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}