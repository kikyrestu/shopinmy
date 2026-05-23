<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $apiUrl = 'https://api.fonnte.com/send';
    
    /**
     * Send WhatsApp Message via Fonnte
     *
     * @param string $target Target phone number(s) comma separated. e.g. "08123456789"
     * @param string $message The message text
     * @param array $options Additional Fonnte options (e.g. delay, typing, url)
     * @return array|bool
     */
    public static function send(string $target, string $message, array $options = [])
    {
        $token = Setting::get('fonnte_token');
        
        if (!$token) {
            Log::warning('Fonnte token not set in Settings.');
            return false;
        }

        $payload = array_merge([
            'target' => $target,
            'message' => $message,
            'countryCode' => '62', // Default to Indonesia
            'typing' => false,
        ], $options);

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', $payload);

            $result = $response->json();
            
            if (!$response->successful() || (isset($result['status']) && $result['status'] === false)) {
                Log::error('Fonnte API Error', ['response' => $result]);
                return false;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Fonnte API Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send Blast Messages with Delay
     */
    public static function sendBlast(string $targets, string $message)
    {
        // For blast, we enforce typing indicator and random delay 15-45 seconds
        // to mimic human behavior and prevent ban.
        return self::send($targets, $message, [
            'delay' => '15-45',
            'typing' => true,
        ]);
    }
}
