<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    private HttpClientInterface $httpClient;
    private string $secretKey;
    
    public function __construct(HttpClientInterface $httpClient, string $secretKey = null)
    {
        $this->httpClient = $httpClient;
        $this->secretKey = $secretKey ?? 'default';
    }
    
    public function isValid(string $recaptchaResponse): bool
    {
        try {
            $response = $this->httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $this->secretKey,
                    'response' => $recaptchaResponse
                ]
            ]);
            
            $data = $response->toArray();
            return $data['success'] ?? false;
            
        } catch (\Exception $e) {
            // Log error if needed
            return false;
        }
    }
}