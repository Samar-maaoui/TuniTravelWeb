<?php
namespace App\Controller;

use App\Entity\Hotel;
use App\Entity\ReservationHotel;
use App\Form\ReservationHotelType;
use App\Repository\HotelRepository;
use App\Repository\ReservationHotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationHotelController extends AbstractController
{
    #[Route('/ajouter/{id}', name: 'ajouter_reservation')]
    public function ajouter(int $id, HotelRepository $hotelRepo, Request $request, EntityManagerInterface $em): Response
    {
        $hotel = $hotelRepo->find($id);
        $reservation = new ReservationHotel();
        $reservation->setHotel($hotel);

        $form = $this->createForm(ReservationHotelType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reservation);
            $em->flush();
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('hotel/ajouter_reservation_hotels.html.twig', [
            'form' => $form->createView(),
            'hotel' => $hotel,
        ]);
    }
    #[Route('/afficher', name: 'afficher_reservation')]
    public function afficher(ReservationHotelRepository $reservationRepo): Response
    {
        $reservations = $reservationRepo->findAll();
    
        return $this->render('hotel/afficher_reservation_hotels.html.twig', [
            'reservations' => $reservations,
        ]);
    }
  
  

    #[Route('/statistiques', name: 'admin_statistics')]
    public function statistiques(ReservationHotelRepository $reservationRepo, HotelRepository $hotelRepo): Response
    {
        // Récupérer tous les hôtels
        $hotels = $hotelRepo->findAll();
        
        // Initialiser un tableau pour stocker le nombre de réservations par hôtel
        $reservationsCount = [];
        
        foreach ($hotels as $hotel) {
            // Compter le nombre de réservations pour chaque hôtel
            $reservationsCount[$hotel->getNom()] = count($reservationRepo->findBy(['hotel' => $hotel]));
        }
    
        return $this->render('hotel/statistiques.html.twig', [
            'reservationsCount' => $reservationsCount,
        ]);
    }
#[Route('/modifier/{id}', name: 'modifier_reservation')]
public function modifier(
    ReservationHotel $reservation, 
    Request $request, 
    EntityManagerInterface $em
): Response
{
    // Sauvegarde des dates originales pour comparaison
    $originalCheckIn = clone $reservation->getCheckInDate();
    $originalCheckOut = clone $reservation->getCheckOutDate();

    $form = $this->createForm(ReservationHotelType::class, $reservation);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            // Vérification supplémentaire des dates
            if ($reservation->getCheckOutDate() >= $reservation->getCheckInDate()) {
                $this->addFlash('error', 'La date de départ doit être avant la date d\'arrivée.');
            } else {
                try {
                    $em->flush();
                    return $this->redirectToRoute('afficher_reservation');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue : ' . $e->getMessage());
                    // Restauration des dates originales en cas d'erreur
                    $reservation->setCheckInDate($originalCheckIn);
                    $reservation->setCheckOutDate($originalCheckOut);
                }
            }
        }
        
        // Afficher toutes les erreurs de validation
        foreach ($form->getErrors(true) as $error) {
            $this->addFlash('error', $error->getMessage());
        }
    }

    return $this->render('hotel/modifier_reservation_hotels.html.twig', [
        'form' => $form->createView(),
        'reservation' => $reservation,
        'hotel' => $reservation->getHotel(),
    ]);
}

    #[Route('/supprimer/{id}', name: 'supprimer_reservation')]
    public function supprimer(ReservationHotel $reservation, EntityManagerInterface $em): Response
    {
        $hotelId = $reservation->getHotel()->getIdHotel();
        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('afficher_reservation');
    }
 
    #[Route('/admin/reservations', name: 'admin_afficher_reservations')]
    public function afficherReservations(ReservationHotelRepository $reservationRepo): Response
    {
        $reservations = $reservationRepo->findAll();

        return $this->render('hotel/afficher_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }
}

