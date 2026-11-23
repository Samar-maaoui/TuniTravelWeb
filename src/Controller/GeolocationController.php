<?php

namespace App\Controller;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class GeolocationController extends AbstractController
{
    private HttpClientInterface $client;
    private string $locationIqKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->locationIqKey = $_ENV['LOCATIONIQ_API_KEY']; // bonne pratique : clé dans .env
    }

    #[Route('/api/geocode/{lieu}', name: 'api_geocode', methods: ['GET'])]
    public function geocode(string $lieu): JsonResponse
    {
        $url = 'https://us1.locationiq.com/v1/search.php';

        try {
            $response = $this->client->request('GET', $url, [
                'query' => [
                    'key' => $this->locationIqKey,
                    'q' => $lieu,
                    'format' => 'json'
                ]
            ]);

            $data = $response->toArray();

            if (!empty($data[0])) {
                return $this->json([
                    'lat' => $data[0]['lat'],
                    'lon' => $data[0]['lon']
                ]);
            }

            return $this->json(['error' => 'Lieu non trouvé'], 404);

        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur API : ' . $e->getMessage()], 500);
        }
    }
}
