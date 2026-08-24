<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsappService
{
    public function enabled(): bool
    {
        return config('services.whatsapp.enabled') && !empty(config('services.whatsapp.api_url')) && !empty(config('services.whatsapp.token'));
    }

    public function send(string $phone, string $message): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $phoneNumber = preg_replace('/[^0-9]/', '', $phone);
        if (! str_starts_with($phoneNumber, '55')) {
            $phoneNumber = '55' . $phoneNumber;
        }

        $url = config('services.whatsapp.api_url');
        $token = config('services.whatsapp.token');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, $payload);

        return $response->successful();
    }

    public function createLink(string $phone, string $message): string
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phone);
        if (! str_starts_with($phoneNumber, '55')) {
            $phoneNumber = '55' . $phoneNumber;
        }

        return 'https://api.whatsapp.com/send?phone=' . $phoneNumber . '&text=' . urlencode($message);
    }
}
