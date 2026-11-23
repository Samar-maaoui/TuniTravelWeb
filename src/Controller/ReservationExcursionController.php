<?php

namespace App\Controller;
use App\Service\BrevoEmailService;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\ReservationExcursion;
use App\Entity\Reservation;
use App\Entity\Excursion;
use App\Entity\Utilisateur as EntityUtilisateur;
use App\Form\UtilisateurType;
use src\Entity\Utilisateur;
use App\Repository\ReservationExcursionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Proxies\__CG__\App\Entity\Utilisateur as AppEntityUtilisateur;

class ReservationExcursionController extends AbstractController
{
    #[Route('/reservations', name: 'app_reservations_list', methods: ['GET'])]
    public function listReservations(ReservationExcursionRepository $resEx): Response
    {
        // Récupérer toutes les réservations d'excursions
        $reservations = $resEx->createQueryBuilder('re')
            ->getQuery()
            ->getResult();
    
        return $this->render('reservation_excursion/reservation_list.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/mes-reservationsExcursions', name: 'app_mes_reservationsExcursions')]
    public function mesReservations(ReservationExcursionRepository $resEx): Response
    {
       // Récupérer l'utilisateur connecté
$user = $this->getUser();

// Si l'utilisateur n'est pas connecté, lancer une exception
if (!$user) {
    throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos réservations.');
}

// Récupérer toutes les réservations pour l'utilisateur connecté
$reservations = $resEx->createQueryBuilder('re')
    ->join('re.utilisateur', 'u') // Joindre la relation utilisateur
    ->addSelect('u') // Charger aussi les informations de l'utilisateur
    ->where('re.utilisateur = :user') // Filtrer par l'utilisateur connecté
    ->setParameter('user', $user) // Passer l'utilisateur en paramètre
    ->getQuery()
    ->getResult();
    
        // Retourner la vue avec les réservations
        return $this->render('reservation_excursion/listeClient.html.twig', [
            'reservations' => $reservations,
        ]);
    }
    
    #[Route('/reservation/annuler/{id}', name: 'app_reservation_annuler', methods: ['POST'])]
    public function annulerReservation(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservationExcursion = $entityManager->getRepository(ReservationExcursion::class)->find($id);
    
        if ($reservationExcursion) {
            // Récupère l'ID de la réservation
            $idReservation = $reservationExcursion->getIdReservation();
    
            // Récupère l'objet Reservation depuis son ID
            $reservation = $entityManager->getRepository(ReservationExcursion::class)->find($idReservation);
    
            if ($reservation) {
                $entityManager->remove($reservation);
                $entityManager->flush();
    
                $this->addFlash('success', 'La réservation a été annulée avec succès.');
            } else {
                $this->addFlash('error', 'Réservation liée introuvable.');
            }
        } else {
            $this->addFlash('error', 'Réservation Excursion introuvable.');
        }
    
        return $this->redirectToRoute('app_excursion_index'); }
    


        #[Route('/reservationExcursion/{id}', name: 'app_reservation_excursion', methods: ['GET','POST'])]
        public function reservation(
            Request $request,
            EntityManagerInterface $entityManager,
            Excursion $excursion
        ): Response {
            /** @var Utilisateur $user */
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour réserver une excursion.');
                return $this->redirectToRoute('app_login');
            }
    
            $numPlaces = (int) $request->request->get('numPlaces');
            if ($numPlaces <= 0) {
                $this->addFlash('error', 'Veuillez entrer un nombre de places valide.');
                return $this->redirectToRoute('excursion_show', ['id' => $excursion->getIdExcursion()]);
            }
            if ($numPlaces > $excursion->getNombreMaxParticipants()) {
                $this->addFlash('error', 'Il n\'y a pas assez de places disponibles.');
                return $this->redirectToRoute('app_dashboard');
            }
    
            // Calcul du montant total en centimes
            $prixExcursion = $excursion->getPrix();
            $montantTotal = $numPlaces * $prixExcursion;
    
            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'aed',
                        'unit_amount' => (int)($montantTotal * 100),
                        'product_data' => [
                            'name' => 'Réservation Excursion: ' . $excursion->getNom(),
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                // ← UN seul niveau d’accolades pour que Stripe remplace {CHECKOUT_SESSION_ID}
                'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
                                 . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => $this->generateUrl('payment_cancel',  [], UrlGeneratorInterface::ABSOLUTE_URL),
               'metadata'    => [
    'excursion_id'  => $excursion->getIdExcursion(),
    'user_id'       => $user->getId(),
    'guide_id'      => $excursion->getGuide()?->getIdGuide() ?? null, // ✅ ici
    'num_places'    => $numPlaces,
    'montant_total' => $montantTotal,
],

            ]);
    
            return $this->redirect($session->url, 303);
        }
    
        #[Route('/payment-success', name: 'payment_success')]
        public function paymentSuccess(
            Request $request,
            EntityManagerInterface $entityManager,
            BrevoEmailService $brevoEmailService
        ): Response {
            $sessionId = trim($request->query->get('session_id', ''), '{}');
            if (!$sessionId) {
                throw $this->createNotFoundException('Session Stripe manquante.');
            }
        
            Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
            $session = Session::retrieve($sessionId);
            $m = $session->metadata;
        
            // Récupère les entités
            $excursion = $entityManager->getRepository(Excursion::class)->find($m->excursion_id);
            $user      = $entityManager->getRepository(EntityUtilisateur::class)->find($m->user_id);
            $places    = $m->num_places;
            $total     = $m->montant_total;
        
            if (!$excursion || !$user) {
                $this->addFlash('error', 'Impossible de retrouver excursion ou utilisateur.');
                return $this->redirectToRoute('app_dashboard');
            }
        
            // Persiste la réservation
            $reservation = new ReservationExcursion();
$reservation->setExcursion($excursion)
            ->setUtilisateur($user)
            ->setGuide($excursion->getGuide()) // ✅ Ajout ici
            ->setNbrPlaceReservee($places)
            ->setMontantTotal($total)
            ->setDateReservation(new \DateTime())
            ->setStatut('CONFIRMEE');

            // Mise à jour des places restantes
            $excursion->setNombreMaxParticipants(
                $excursion->getNombreMaxParticipants() - $places
            );
        
            $entityManager->persist($excursion);
            $entityManager->persist($reservation);
            $entityManager->flush();
        
            // ✅ Envoi d'email de confirmation à l'utilisateur
            $brevoEmailService->sendEmail(
                $user->getEmail(),
                'Confirmation de réservation',
                "
                <div style=\"font-family: Arial, sans-serif; padding: 20px; background-color: #f9f9f9; border-radius: 8px;\">
                    <h2 style=\"color: #1E3A8A;\">Bonjour {$user->getPrenom()} !</h2>
                    <p style=\"font-size: 16px; color: #333;\">
                        Votre réservation pour <strong style=\"color: #B8860B;\">{$excursion->getNom()}</strong> a bien été confirmée.
                    </p>
                    <p style=\"font-size: 14px; color: #555;\">
                        <strong>Nombre de places :</strong> {$places}<br>
                        <strong>Montant total :</strong> {$total} TND<br>
                        <strong>Date :</strong> " . (new \DateTime())->format('d/m/Y H:i') . "
                    </p>
                    <hr style=\"margin: 20px 0; border: none; border-top: 1px solid #ddd;\">
                    <p style=\"font-size: 14px; color: #777;\">
                        Merci de votre confiance. <br>
                        L’équipe ITENERVES.
                    </p>
                </div>
                ",
                null,
                "Votre réservation pour {$excursion->getNom()} est confirmée."
            );
            
        
            // Redirige vers la génération du QR
            return $this->redirectToRoute('reservation_qr_view', [
                'id' => $reservation->getIdReservation()
            ]);
        }
        
        #[Route('/payment-cancel', name: 'payment_cancel')]
        public function paymentCancel(): Response
        {
            $this->addFlash('error', 'Paiement annulé.');
            return $this->redirectToRoute('app_dashboard');
        }
    }
        

