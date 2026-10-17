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
    public static function send($driver, string $message): void
    {
        $phone = $driver->contact_number;
        $driverId = $driver->id;

        // Always save to database logs for the in-app simulator
        SmsLog::create([
            'driver_id' => $driverId,
            'phone_number' => $phone ?? 'N/A',
            'message' => $message,
        ]);

        if (empty($phone)) {
            Log::warning("Cannot send SMS: Driver {$driver->name} has no contact number.");
            return;
        }

        $sent = false;

        // 1. Check for TextBee (Android Phone Gateway with personal SIM)
        $textbeeKey = config('services.textbee.key');
        $textbeeDeviceId = config('services.textbee.device_id');
        if (!empty($textbeeKey) && !empty($textbeeDeviceId)) {
            try {
                $recipientPhone = $phone;
                if (str_starts_with($recipientPhone, '09')) {
                    $recipientPhone = '+63' . substr($recipientPhone, 1);
                } elseif (str_starts_with($recipientPhone, '9') && strlen($recipientPhone) === 10) {
                    $recipientPhone = '+63' . $recipientPhone;
                }

                $response = Http::timeout(10)->withHeaders([
                    'x-api-key' => $textbeeKey,
                ])->post("https://api.textbee.dev/api/v1/gateway/devices/{$textbeeDeviceId}/send-sms", [
                    'recipients' => [$recipientPhone],
                    'message' => $message,
                ]);

                if ($response->successful()) {
                    Log::info("TextBee Android Gateway SMS sent successfully to {$driver->name} ({$recipientPhone}).");
                    $sent = true;
                } else {
                    Log::error("TextBee Android Gateway failed to send to {$driver->name}. Response: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Error sending SMS via TextBee: " . $e->getMessage());
            }
        }

        // 2. Check for Semaphore API
        $semaphoreKey = config('services.semaphore.key');
        $semaphoreSender = config('services.semaphore.sender');
        if (!$sent && !empty($semaphoreKey)) {
            try {
                $response = Http::timeout(10)->post('https://api.semaphore.co/api/v4/messages', [
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
            } catch (\Exception $e) {
                Log::error("Error sending SMS via Semaphore: " . $e->getMessage());
            }
        }

        if (!$sent) {
            // Log for local development / simulation
            Log::info("SMS simulated (no real SMS gateway configured or dispatch finished) for {$driver->name} ({$phone}):\n{$message}");
        }
    }
}
