<?php

namespace App\Controller;

use App\Entity\ReservationHotel;
use App\Service\PdfService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HotelRepository;
use App\Repository\ReservationHotelRepository;
use Symfony\Component\HttpFoundation\Request;
class PdfController extends AbstractController
{
    #[Route('/pdf/{id}', name: 'reservation_pdf')]
    public function generatePdf(ReservationHotel $reservation, PdfService $pdfService): Response
    {
        $html = $this->renderView('hotel/reservation_pdf.html.twig', [
            'reservation' => $reservation,
            'hotel' => $reservation->getHotel()
        ]);

        $pdfContent = $pdfService->generatePdf($html);

        $filename = sprintf('reservation-hotel-%d-%s.pdf', 
            $reservation->getIdReservation(),
            date('Y-m-d')
        );

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
            ]
        );
    }
    #[Route('/pdf/hotel/{id}', name: 'hotel_reservations_pdf')]
public function generateHotelReservationsPdf(
    int $id,
    HotelRepository $hotelRepo,
    ReservationHotelRepository $reservationRepo,
    PdfService $pdfService
): Response {
    $hotel = $hotelRepo->find($id);
    $reservations = $reservationRepo->findBy(['hotel' => $hotel]);

    $html = $this->renderView('hotel/pdf_reservations_par_hotel.html.twig', [
        'hotel' => $hotel,
        'reservations' => $reservations
    ]);

    $pdfContent = $pdfService->generatePdf($html);

    $filename = sprintf('reservations-%s-%s.pdf', 
        strtolower(str_replace(' ', '_', $hotel->getNom())),
        date('Y-m-d')
    );

    return new Response(
        $pdfContent,
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename)
        ]
    );
}
}
