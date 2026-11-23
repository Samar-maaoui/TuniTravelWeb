<?php
// src/Controller/QrReservationController.php

namespace App\Controller;

use App\Entity\ReservationExcursion;
use App\Service\QrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrReservationController extends AbstractController
{
    public function __construct(private QrCodeGenerator $qrCodeGenerator) {}

    // route pour générer le PNG pur (ton code existant).....
    #[Route('/reservation/{id}/qr', name: 'reservation_qr')]
    public function qr(ReservationExcursion $reservation): Response
    {
        $data = [
            'excursion'  => $reservation->getExcursion()->getNom(),
            'user_email' => $reservation->getUtilisateur()->getEmail(),
            'places'     => $reservation->getNbrPlaceReservee(),
            'total'      => $reservation->getMontantTotal(),
            'date'       => $reservation->getDateReservation()->format('Y-m-d H:i'),
        ];
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        $pngData = $this->qrCodeGenerator->generate($payload);

        return new Response(
            $pngData,
            200,
            ['Content-Type' => $this->qrCodeGenerator->getContentType()]
        );
    }

    // nouvelle route pour afficher la page avec le QR + bouton + texte
    #[Route('/reservation/{id}/qr-view', name: 'reservation_qr_view')]
    public function qrView(ReservationExcursion $reservation): Response
    {
        return $this->render('reservation_excursion\qr_view.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
