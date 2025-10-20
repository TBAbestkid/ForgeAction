<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoMailService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('MAIL_BREVO_API_KEY');
    }

    /**
     * Envia e-mail via API Brevo
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $htmlContent
     * @return bool
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        // dump($response->status());
        // dump($response->body());

        return $response->successful();
    }
}
