<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PredictHQService
{
    private $client;
    private $accessToken;

    public function __construct(HttpClientInterface $client, string $predictHQAccessToken)
    {
        $this->client = $client;
        $this->accessToken = $predictHQAccessToken;
    }

    public function searchEvents(string $location, ?string $category = null): array
    {
        $queryParams = [
            'within' => "10km@$location", 
            'limit' => 10,
        ];

        if ($category) {
            $queryParams['category'] = $category;
        }

        $response = $this->client->request('GET', 'https://api.predicthq.com/v1/events/', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
            ],
            'query' => $queryParams,
        ]);

        return $response->toArray();
    }
}
