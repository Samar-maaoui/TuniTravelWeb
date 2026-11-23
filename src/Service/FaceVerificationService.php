<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class FaceVerificationService
{
    private $client;
    private $apiKey;
    private $apiSecret;

    public function __construct(HttpClientInterface $client, string $apiKey, string $apiSecret)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public function verifyFaces(string $imagePath1, string $imagePath2, int $threshold = 75): array
{
    if (!file_exists($imagePath1) || !@getimagesize($imagePath1)) {
        throw new \Exception('Image 1 is invalid or not found: ' . $imagePath1);
    }

    if (!file_exists($imagePath2) || !@getimagesize($imagePath2)) {
        throw new \Exception('Image 2 is invalid or not found: ' . $imagePath2);
    }

    $response = $this->client->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/compare', [
        'body' => [
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'image_file1' => fopen($imagePath1, 'rb'),
            'image_file2' => fopen($imagePath2, 'rb'),
        ],
    ]);

    try {
        $data = $response->toArray();
    } catch (ClientExceptionInterface $e) {
        $data = json_decode($response->getContent(false), true);
    }

    return $data; // <<< on retourne tout

}}
