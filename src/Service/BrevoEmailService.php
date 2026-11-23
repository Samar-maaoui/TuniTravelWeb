<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BrevoEmailService
{
    private Client $client;
    private string $apiKey;
    private string $defaultFrom;
    private ?LoggerInterface $logger;

    public function __construct(
        string $apiKey,
        string $defaultFrom,
        ?LoggerInterface $logger = null
    ) {
        $this->client = new Client();
        $this->apiKey = $apiKey;
        $this->defaultFrom = $defaultFrom;
        $this->logger = $logger;
    }
    public function sendEmail(
        string $to,
        string $subject,
        string $htmlContent,
        ?string $from = null,
        ?string $textContent = null
    ): array {
        $payload = [
            'sender' => ['email' => $from ?? $this->defaultFrom],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        if ($textContent) {
            $payload['textContent'] = $textContent;
        }

        try {
            $response = $this->client->post('https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $this->apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => $payload
            ]);

            return json_decode($response->getBody()->getContents(), true);
            
        } catch (GuzzleException $e) {
            $this->logger?->error('Brevo API Error: '.$e->getMessage());
            throw new \RuntimeException('Failed to send email: '.$e->getMessage());
        }
    }
}