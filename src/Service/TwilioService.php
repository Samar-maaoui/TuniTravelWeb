<?php

namespace App\Service;

use Twilio\Rest\Client;

class TwilioService
{
    private $accountSid;
    private $authToken;
    private $fromNumber;

    public function __construct(string $accountSid, string $authToken, string $fromNumber)
    {
        $this->accountSid = $accountSid;
        $this->authToken = $authToken;
        $this->fromNumber = $fromNumber;
    }

    public function sendWhatsAppMessage(string $to, string $message): void
    {
        $client = new Client($this->accountSid, $this->authToken);

        $client->messages->create(
            "whatsapp:$to",
            [
                'from' => "whatsapp:{$this->fromNumber}",
                'body' => $message
            ]
        );
    }
}