<?php

namespace App\Service;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Resolve the Firebase service account JSON file path, supporting both explicit config
     * and a single uploaded `.json` file inside `storage/app/firebase/`.
     */
    public function resolveCredentialsPath(): ?string
    {
        $configured = trim((string) config('services.firebase.credentials', config('firebase.credentials', '')));
        if ($configured !== '') {
            $candidate = str_starts_with($configured, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\/\\\\]/', $configured)
                ? $configured
                : base_path($configured);

            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $matches = glob(storage_path('app/firebase/*.json')) ?: [];
        if (count($matches) === 1 && is_file($matches[0])) {
            return $matches[0];
        }

        return null;
    }

    /**
     * Resolve Firebase Project ID from config or directly from the service account JSON.
     */
    public function resolveProjectId(?string $credentialsPath = null): ?string
    {
        $projectId = trim((string) config('services.firebase.project_id', ''));
        if ($projectId !== '') {
            return $projectId;
        }

        $credentialsPath ??= $this->resolveCredentialsPath();
        if ($credentialsPath && is_file($credentialsPath)) {
            $json = json_decode((string) file_get_contents($credentialsPath), true);
            if (! empty($json['project_id'])) {
                return trim((string) $json['project_id']);
            }
        }

        return null;
    }

    /**
     * Check whether Firebase project ID and credentials JSON exist on disk.
     */
    public function isConfigured(): bool
    {
        $credentialsPath = $this->resolveCredentialsPath();

        return $credentialsPath !== null && $this->resolveProjectId($credentialsPath) !== null;
    }

    private function accessToken(?string $credentialsPath = null): ?string
    {
        $credentialsPath ??= $this->resolveCredentialsPath();
        if (! $credentialsPath) {
            return null;
        }

        $cacheKey = 'firebase_oauth_token_' . md5($credentialsPath . '|' . (@filemtime($credentialsPath) ?: 0));
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $json = json_decode((string) file_get_contents($credentialsPath), true);
            if (! is_array($json)) {
                Log::error('Firebase credentials JSON is invalid: ' . $credentialsPath);
                return null;
            }

            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/firebase.messaging'],
                $json
            );

            $credentials->fetchAuthToken();
            $token = $credentials->getLastReceivedToken()['access_token'] ?? null;

            if ($token) {
                Cache::put($cacheKey, $token, 3000);
            }

            return $token;
        } catch (\Throwable $e) {
            Log::error('Firebase OAuth token fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send an FCM v1 notification to a single device token.
     *
     * @param  array<string, mixed>  $data
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        $credentialsPath = $this->resolveCredentialsPath();
        $projectId = $this->resolveProjectId($credentialsPath);

        if (! $credentialsPath || ! $projectId) {
            Log::warning('Firebase notification skipped: missing FIREBASE_PROJECT_ID or credentials JSON.');
            return ['success' => false, 'skipped' => true];
        }

        $oauthToken = $this->accessToken($credentialsPath);
        if (! $oauthToken) {
            return ['success' => false, 'skipped' => true];
        }

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body' => $body,
            ],
        ];

        $stringData = $this->formatDataPayload($data);
        if ($stringData !== []) {
            $message['data'] = $stringData;
        }

        $response = Http::withToken($oauthToken)
            ->post(
                "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send",
                ['message' => $message]
            );

        if ($response->failed()) {
            Log::warning('Firebase FCM v1 send failed.', [
                'project_id' => $projectId,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        }

        return $response->json();
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
