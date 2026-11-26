<?php

namespace App\Service;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Check whether Firebase project ID and credentials JSON exist on disk.
     */
    public function isConfigured(): bool
    {
        $projectId = env('FIREBASE_PROJECT_ID');
        $credentialsRel = env('FIREBASE_CREDENTIALS');

        if (empty($projectId) || empty($credentialsRel)) {
            return false;
        }

        return is_file(base_path($credentialsRel));
    }

    private function accessToken()
    {
        $credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            json_decode(
                file_get_contents(base_path(env('FIREBASE_CREDENTIALS'))),
                true
            )
        );

        $credentials->fetchAuthToken();
        return $credentials->getLastReceivedToken()['access_token'];
    }

    /**
     * Send an FCM v1 notification to a single device token.
     *
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        if (! $this->isConfigured()) {
            Log::warning('Firebase notification skipped: missing FIREBASE_PROJECT_ID or credentials JSON.');
            return ['success' => false, 'skipped' => true];
        }

        $message = [
            "token" => $token,
            "notification" => [
                "title" => $title,
                "body"  => $body,
            ],
        ];

        $stringData = $this->formatDataPayload($data);
        if ($stringData !== []) {
            $message['data'] = $stringData;
        }

        return Http::withToken($this->accessToken())
            ->post(
                "https://fcm.googleapis.com/v1/projects/" . env('FIREBASE_PROJECT_ID') . "/messages:send",
                [
                    "message" => $message,
                ]
            )
            ->json();
    }

    /**
     * Normalize FCM v1 message data entries to strings as required by Google's API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function formatDataPayload(array $data): array
    {
        $formatted = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $formatted[(string) $key] = $value ? 'true' : 'false';
                continue;
            }

            $formatted[(string) $key] = is_scalar($value)
                ? (string) $value
                : json_encode($value);
        }

        return $formatted;
    }
}
