<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class WorkAroundCompleteNotification
{
    protected $appId;
    protected $apiKey;

    public function __construct()
    {
        // Set your API key here or retrieve it from your .env file
        $this->apiKey = 'NWYwNDQxMGQtNTBiNy00YjkwLWE2MTctZDc5ZDE3YWE2YjNh';
        $this->appId = '9dfb7061-e995-464b-aa70-9d8961e29dfe'; // Use your actual app ID
    }

   public function send(array $data)
{
    $tokens = $data['tokens'] ?? [];

    // 🔥 FIX: Ensure proper indexed array
    $tokens = array_values($tokens);

    \Log::info("Sending notification with tokens: " . implode(', ', $tokens));

    $response = Http::withHeaders([
        'Authorization' => 'Basic ' . $this->apiKey,
        'Content-Type' => 'application/json',
    ])->post('https://onesignal.com/api/v1/notifications', [
        'app_id' => $this->appId,
        'include_player_ids' => $tokens,
        'headings' => ['en' => $data['title']],
        'contents' => ['en' => $data['message']],
    ]);

    Log::info("Response: " . $response->body());

    if ($response->successful()) {
        \Log::info("OneSignal notification sent successfully: " . $response->body());
    } else {
        \Log::error("Failed to send OneSignal notification: " . $response->body());
    }
}
}



