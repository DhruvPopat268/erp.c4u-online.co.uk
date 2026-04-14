<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $appId;
    protected $apiKey;

    public function __construct()
    {
        $this->appId = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.rest_api_key');
    }

 public function send(array $data)
    {
        // Include FCM tokens if provided
        $tokens = $data['tokens'] ?? [];

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => $this->appId,
            'include_player_ids' => $tokens, // Use FCM tokens to target specific devices
            'headings' => ['en' => $data['title']],
            'contents' => ['en' => $data['message']],
            // Add other parameters here if necessary, such as URL, buttons, etc.
        ]);

        if ($response->successful()) {
            \Log::info("OneSignal notification sent successfully: " . $response->body());
        } else {
            \Log::error("Failed to send OneSignal notification: " . $response->body());
        }
    }





}


