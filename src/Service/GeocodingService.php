<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function geocode(string $place): ?array
    {
        $response = $this->client->request('GET', 'https://nominatim.openstreetmap.org/search', [
            'query' => [
                'q' => $place,
                'format' => 'json',
                'limit' => 1,
            ],
            'headers' => [
                'User-Agent' => 'SymfonyPredictHQApp/1.0',
            ]
        ]);

        $data = $response->toArray();

        if (empty($data)) {
            return null;
        }

        return [
            'lat' => $data[0]['lat'],
            'lng' => $data[0]['lon'],
        ];
    }
}
