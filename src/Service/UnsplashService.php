<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class UnsplashService
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $unsplashApiKey)
    {
        $this->client = $client;
        $this->apiKey = $unsplashApiKey;
    }

    public function searchImage(string $query): ?string
    {
        $response = $this->client->request('GET', 'https://api.unsplash.com/search/photos', [
            'query' => [
                'query' => $query,
                'client_id' => $this->apiKey,
                'per_page' => 1,
            ],
        ]);

        $data = $response->toArray();

        if (!empty($data['results'])) {
            // On récupère l'image de taille "regular" pour une meilleure qualité
            return $data['results'][0]['urls']['regular'] ?? null;
        }

        return null;
    }
}
