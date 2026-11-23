<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class WeatherController extends AbstractController
{
    private HttpClientInterface $client;
    private string $weatherApiKey = '874a1f2bd3d68dc4b74319cbe516e24f'; // Mets ta clé ici

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    #[Route('/weather', name: 'weather')]
    public function index(Request $request): Response
    {
        $city = $request->query->get('city');
    
        $data = null;
    
        if ($city) {
            $response = $this->client->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->weatherApiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);
    
            $data = $response->toArray();
        }
    
        return $this->render('weather/index.html.twig', [
            'weather' => $data,
            'city' => $city,
        ]);
    }
    
    
}