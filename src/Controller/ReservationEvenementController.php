<?php

namespace App\Controller;
use App\Entity\reservationevenement;
use App\Entity\Evenements;
use App\Form\ReservationEvenementType;
use App\Repository\ReservationEvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface; // Make sure to import the LoggerInterface

#[Route('/reservation/evenement')]
final class ReservationEvenementController extends AbstractController
{
    #[Route(name: 'app_reservation_evenement_index', methods: ['GET'])]
    public function index(ReservationEvenementRepository $reservationEvenementRepository): Response
    {
        $reservations = $reservationEvenementRepository->createQueryBuilder('r')
            ->leftJoin('r.utilisateur', 'u')
            ->leftJoin('r.evenement', 'e')
            ->addSelect('u', 'e')
            ->getQuery()
            ->getResult();

        return $this->render('reservation_evenement/index.html.twig', [
            'reservation_evenements' => $reservations,
        ]);
    }

    #[Route('/reservation/new/{id}', name: 'reservation_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        Evenements $evenement,  // This will automatically fetch the event by ID
        LoggerInterface $logger  // Inject the custom logger service
    ): Response {
        $reservationEvenement = new ReservationEvenement();

        // Set the event automatically
        $reservationEvenement->setDateReservation(new \DateTime('now', new \DateTimeZone('Africa/Tunis')));
        $reservationEvenement->setEvenement($evenement);

        $user = $this->getUser();  // Assuming the user is logged in and implements UserInterface

        // Set the logged-in user to the reservation
        if ($user) {
            $reservationEvenement->setUtilisateur($user);  // Make sure setUtilisateur() is a method in your ReservationEvenement entity
        }

        // Create and handle the form
        $form = $this->createForm(ReservationEvenementType::class, $reservationEvenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist reservation
            $entityManager->persist($reservationEvenement);
            $entityManager->flush();

            // Log the reservation creation
            $logger->info('New reservation created', [
                'user_id' => $user ? $user->getId() : null,
                'event_id' => $evenement->getId(),
                'places' => $reservationEvenement->getNombrePlaces(),
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            // Store reservation data in a JSON file
            $reservationData = [
                'user_id' => $user ? $user->getId() : null,
                'user_nom' => $user ? $user->getNom() : null,
                'user_prenom' => $user ? $user->getPrenom() : null,
                'user_email' => $user ? $user->getEmail() : null,
                'user_phone' => $user ? $user->getTelephone() : null,
                'event_id' => $evenement->getId(),
                'event_name' => $evenement->getNom(), // Assuming you have a `getNom()` method for the event name
                'places' => $reservationEvenement->getNombrePlaces(),
                'date_reservation' => $reservationEvenement->getDateReservation() ? $reservationEvenement->getDateReservation()->format('Y-m-d H:i:s') : null,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),
            ];

            // Define the path to store the JSON file
            $filePath = $this->getParameter('kernel.project_dir') . '/var/reservations.json';

            // Check if the file exists
            if (file_exists($filePath)) {
                // Append the new reservation data to the existing JSON file
                $existingData = json_decode(file_get_contents($filePath), true);
                $existingData[] = $reservationData;  // Add new data to the array

                // Save the updated data back to the JSON file
                file_put_contents($filePath, json_encode($existingData, JSON_PRETTY_PRINT));
            } else {
                // If file doesn't exist, create a new one with the current reservation data
                file_put_contents($filePath, json_encode([$reservationData], JSON_PRETTY_PRINT));
            }

            // Redirect after successful reservation
            return $this->redirectToRoute('app_dashboard');
        }

        // Render the form in the template
        return $this->render('reservation_evenement/new.html.twig', [
            'reservation_evenement' => $reservationEvenement,
            'form' => $form->createView(),
            'event' => $evenement, // Pass the event to the template if needed
        ]);
    }
    #[Route('/{id_reservation}', name: 'app_reservation_evenement_show', methods: ['GET'])]
    public function show(ReservationEvenement $reservationEvenement): Response
    {
        return $this->render('reservation_evenement/show.html.twig', [
            'reservation_evenement' => $reservationEvenement,
        ]);
    }

    #[Route('/{id_reservation}', name: 'app_reservation_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, ReservationEvenement $reservationEvenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationEvenement->getIdReservation(), $request->get('_token'))) {
            $entityManager->remove($reservationEvenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_evenement_index', [], Response::HTTP_SEE_OTHER);
    }
}
