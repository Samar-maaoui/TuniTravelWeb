<?php

namespace App\Controller;

use App\Service\PredictHQService;
use App\Service\GeocodingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PredictHQController extends AbstractController
{
    #[Route('/predicthq', name: 'app_predicthq')]
    #[Route('/predicthq/place/{place}', name: 'app_predicthq_place')]
    public function searchByPlace(
        Request $request, 
        PredictHQService $predictHQService, 
        GeocodingService $geocodingService, 
        string $place = null
    ): Response {
        $place = $place ?? $request->query->get('place');
        $events = [];
        $coordinates = null;

        if ($place) {
            $coordinates = $geocodingService->geocode($place);

            if ($coordinates) {
                // Si les coordonnées sont trouvées, chercher les événements PredictHQ
                $location = $coordinates['lat'] . ',' . $coordinates['lng'];
                $events = $predictHQService->searchEvents($location)['results'] ?? [];
            } else {
                $this->addFlash('error', 'Lieu introuvable.');
            }
        }
        $template = $request->isXmlHttpRequest() ? 'predict_hq/_results.html.twig' : 'predict_hq/index.html.twig';

        return $this->render($template, [
        
            'events' => $events,
            'place' => $place,
            'coordinates' => $coordinates,
        ]);
    }
}
