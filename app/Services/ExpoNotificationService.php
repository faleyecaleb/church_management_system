<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoNotificationService
{
    /**
     * Send a single push notification to an Expo token.
     *
     * @param string $expoToken
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function sendNotification(string $expoToken, string $title, string $body, array $data = []): bool
    {
        if (empty($expoToken)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Accept-encoding' => 'gzip, deflate',
            ])->post('https://exp.host/--/api/v2/push/send', [
                'to' => $expoToken,
                'title' => $title,
                'body' => $body,
                'sound' => 'default',
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Expo API might return success but have internal errors per token
                if (isset($responseData['data']['status']) && $responseData['data']['status'] === 'error') {
                    Log::error('Expo push delivery error details: ' . json_encode($responseData['data']));
                    return false;
                }
                
                return true;
            }

            Log::error('Expo push notification request failed: ' . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error('Exception in ExpoNotificationService: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a push notification to a specific Member if they have a token and push notifications are enabled.
     *
     * @param Member $member
     * @param string $title
     * @param string $body
     * @param array $data
     * @return bool
     */
    public function notifyMember(Member $member, string $title, string $body, array $data = []): bool
    {
        // 1. Check if the member has a registered push token
        if (empty($member->expo_push_token)) {
            return false;
        }

        // 2. Respect member's notification preferences
        $customFields = $member->custom_fields ?? [];
        $pushEnabled = $customFields['push_notifications'] ?? true;

        if (!$pushEnabled) {
            Log::info("Skipping push notification for member {$member->id} because push preferences are disabled.");
            return false;
        }

        return $this->sendNotification($member->expo_push_token, $title, $body, $data);
    }

    /**
     * Send batch notifications to multiple members at once.
     *
     * @param array $members array of Member models
     * @param string $title
     * @param string $body
     * @param array $data
     * @return array array of member IDs that succeeded
     */
    public function notifyMultiple(array $members, string $title, string $body, array $data = []): array
    {
        $payloads = [];
        $memberIdToToken = [];

        foreach ($members as $member) {
            if ($member instanceof Member && !empty($member->expo_push_token)) {
                $customFields = $member->custom_fields ?? [];
                $pushEnabled = $customFields['push_notifications'] ?? true;

                if ($pushEnabled) {
                    $payloads[] = [
                        'to' => $member->expo_push_token,
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'data' => $data,
                        'priority' => 'high',
                    ];
                    $memberIdToToken[$member->expo_push_token] = $member->id;
                }
            }
        }

        if (empty($payloads)) {
            return [];
        }

        $successfulIds = [];

        try {
            // Expo allows sending chunks up to 100 payloads at a time
            $chunks = array_chunk($payloads, 100);

            foreach ($chunks as $chunk) {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Accept-encoding' => 'gzip, deflate',
                ])->post('https://exp.host/--/api/v2/push/send', $chunk);

                if ($response->successful()) {
                    $responseData = $response->json();
                    
                    if (isset($responseData['data']) && is_array($responseData['data'])) {
                        foreach ($responseData['data'] as $index => $status) {
                            $token = $chunk[$index]['to'] ?? null;
                            if ($token && isset($status['status']) && $status['status'] === 'ok') {
                                $successfulIds[] = $memberIdToToken[$token];
                            } else {
                                Log::error('Expo batch delivery error for token ' . ($token ?? 'unknown') . ': ' . json_encode($status));
                            }
                        }
                    }
                } else {
                    Log::error('Expo batch request failed: ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception in Expo batch push send: ' . $e->getMessage());
        }

        return $successfulIds;
    }
}
