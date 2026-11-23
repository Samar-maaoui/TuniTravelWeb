<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;

class PaymeeService
{
    private HttpClientInterface $httpClient;
    private UrlGeneratorInterface $router;
    private LoggerInterface $logger;
    private string $apiKey;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        UrlGeneratorInterface $router,
        LoggerInterface $logger,
        string $apiKey,
        string $apiUrl,
        bool $sandboxMode // Ajout du sandboxMode
    ) {
        $this->httpClient = $httpClient;
        $this->router = $router;
        $this->logger = $logger;
        $this->apiKey = $apiKey;

        // 🟡 Choisir l’URL selon le mode (sandbox vs prod)
        $this->apiUrl = $sandboxMode
            ? 'https://sandbox.paymee.tn/api/v1/payments/create'
            : $apiUrl;
    }

    public function createPayment(
        float $amount,
        string $note,
        string $firstName,
        string $lastName,
        string $email,
        string $phone,
        string $orderId
    ): array {
        try {
            $payload = [
                'amount' => $amount,
                'note' => $note,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'return_url' => $this->router->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->router->generate('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'webhook_url' => $this->router->generate('payment_webhook', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'order_id' => $orderId,
            ];

            $response = $this->httpClient->request('POST', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Token ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $data = $response->toArray();

            if (isset($data['data']['payment_url'])) {
                return [
                    'success' => true,
                    'payment_url' => $data['data']['payment_url'],
                    'transaction_id' => $data['data']['transaction_id']
                ];
            }

            throw new \RuntimeException('Payment URL not found in response');

        } catch (\Exception $e) {
            $this->logger->error('Payment failed', ['error' => $e->getMessage()]);
            throw new \RuntimeException('Payment processing error: ' . $e->getMessage());
        }
    }
}
