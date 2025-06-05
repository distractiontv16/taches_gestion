<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage($phoneNumber, $message)
    {
        try {
            // Configuration Twilio ou WhatsApp Business API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.token'),
            ])->post(config('services.whatsapp.api_url'), [
                'to' => $phoneNumber,
                'message' => $message,
                'from' => config('services.whatsapp.from_phone_number')
            ]);
            
            if ($response->successful()) {
                Log::info('WhatsApp message sent successfully', [
                    'to' => $phoneNumber,
                    'status' => $response->status()
                ]);
                return true;
            } else {
                Log::error('Failed to send WhatsApp message', [
                    'to' => $phoneNumber,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception when sending WhatsApp message', [
                'to' => $phoneNumber,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }
} 