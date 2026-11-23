<?php

/*namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class LocationController extends AbstractController
{
    private $geocoder;

    // Injection du service Geocoder
    public function __construct(Geoco $geocoder)
    {
        $this->geocoder = $geocoder;
    }
    #[Route('/geocode', name: 'geocode')]
    

 
    public function geocode()
    {
        // Exemple de géocodage d'une adresse via Nominatim
        $address = '1600 Amphitheatre Parkway, Mountain View, CA'; // Adresse à géocoder
        try {
            $result = $this->geocoder->geocode($address);

            // Vérifier que des résultats sont retournés
            if (count($result) > 0) {
                $firstResult = $result[0]; // Prendre le premier résultat (le plus pertinent)

                // Récupérer la latitude et la longitude
                $latitude = $firstResult->getLatitude();
                $longitude = $firstResult->getLongitude();

                return new JsonResponse([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            }

            return new JsonResponse(['error' => 'No results found'], 404);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Geocoding failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}*/
