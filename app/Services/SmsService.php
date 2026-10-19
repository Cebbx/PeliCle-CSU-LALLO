<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SmsLog;

class SmsService
{
    /**
     * Send SMS to driver via Android Gateway (TextBee), Semaphore API, or fallback to simulation log.
     */
    public static function send($driver, string $message): bool
    {
        $phone = $driver->contact_number;
        $driverId = $driver->id;

        if (empty($phone)) {
            Log::warning("Cannot send SMS: Driver {$driver->name} has no contact number.");
            return false;
        }

        $sent = false;

        // 1. Check for TextBee (Android Phone Gateway with personal SIM)
        $textbeeKey = config('services.textbee.key');
        $textbeeDeviceId = config('services.textbee.device_id');
        if (!empty($textbeeKey) && !empty($textbeeDeviceId)) {
            $recipientPhone = preg_replace('/[^0-9+]/', '', trim($phone));
            if (str_starts_with($recipientPhone, '09')) {
                $recipientPhone = '+63' . substr($recipientPhone, 1);
            } elseif (str_starts_with($recipientPhone, '9') && strlen($recipientPhone) === 10) {
                $recipientPhone = '+63' . $recipientPhone;
            } elseif (str_starts_with($recipientPhone, '639') && strlen($recipientPhone) === 12) {
                $recipientPhone = '+' . $recipientPhone;
            }

            $url = "https://api.textbee.dev/api/v1/gateway/devices/{$textbeeDeviceId}/send-sms";

            // Attempt 1: Force IPv4 + instant Cloudflare IPv4 resolve (prevents 10s IPv6 DNS timeout on mobile hotspot)
            // Attempt 2: Standard IPv4 DNS fallback if Cloudflare edge IP ever rotates
            $curlConfigs = [
                [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_DNS_CACHE_TIMEOUT => 3600,
                    CURLOPT_TCP_KEEPALIVE => 1,
                    CURLOPT_RESOLVE => [
                        'api.textbee.dev:443:104.21.39.20',
                        'api.textbee.dev:443:172.67.142.54',
                    ],
                ],
                [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_DNS_CACHE_TIMEOUT => 3600,
                ],
            ];

            foreach ($curlConfigs as $idx => $curlOpts) {
                try {
                    $response = Http::connectTimeout(4)
                        ->timeout(8)
                        ->withOptions(['curl' => $curlOpts])
                        ->withHeaders([
                            'x-api-key' => $textbeeKey,
                        ])
                        ->post($url, [
                            'recipients' => [$recipientPhone],
                            'message' => $message,
                        ]);

                    if ($response->successful()) {
                        Log::info("TextBee Android Gateway SMS sent successfully to {$driver->name} ({$recipientPhone}).");
                        $sent = true;
                        break;
                    } else {
                        Log::error("TextBee Android Gateway attempt " . ($idx + 1) . " failed to send to {$driver->name}. Status {$response->status()}: " . $response->body());
                    }
                } catch (\Throwable $e) {
                    Log::warning("TextBee attempt " . ($idx + 1) . " error for {$driver->name}: " . $e->getMessage());
                }
            }
        }

        // 2. Check for Semaphore API
        $semaphoreKey = config('services.semaphore.key');
        $semaphoreSender = config('services.semaphore.sender');
        if (!$sent && !empty($semaphoreKey)) {
            try {
                $response = Http::connectTimeout(4)
                    ->timeout(8)
                    ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                    ->post('https://api.semaphore.co/api/v4/messages', [
                        'apikey' => $semaphoreKey,
                        'number' => $phone,
                        'message' => $message,
                        'sendername' => $semaphoreSender,
                    ]);

                if ($response->successful()) {
                    Log::info("Semaphore SMS sent successfully to {$driver->name} ({$phone}).");
                    $sent = true;
                } else {
                    Log::error("Semaphore SMS failed to send to {$driver->name}. Response: " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("Error sending SMS via Semaphore: " . $e->getMessage());
            }
        }

        // Save to database logs for the in-app simulator
        SmsLog::create([
            'driver_id' => $driverId,
            'phone_number' => $phone ?? 'N/A',
            'message' => $message,
        ]);

        if (!$sent) {
            Log::info("SMS simulated (no real SMS gateway configured or dispatch finished) for {$driver->name} ({$phone}):\n{$message}");
        }

        return $sent;
    }
}
