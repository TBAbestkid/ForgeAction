<?php

namespace App\Mail\Transport;

use Illuminate\Mail\Transport\Transport;
use Swift_Mime_SimpleMessage;
use Illuminate\Support\Facades\Http;

class BrevoTransport extends Transport
{
    protected $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);

        $to = [];
        foreach ($message->getTo() as $email => $name) {
            $to[] = ['email' => $email, 'name' => $name];
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => $message->getFrom()[key($message->getFrom())],
                'email' => key($message->getFrom()),
            ],
            'to' => $to,
            'subject' => $message->getSubject(),
            'htmlContent' => $message->getBody(),
        ]);

        return $response->successful() ? 1 : 0;
    }
}
