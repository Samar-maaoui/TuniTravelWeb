<?php

namespace App\Controller;

use App\Entity\Reservationpackvoyage;
use App\Form\ReservationpackvoyageType;
use App\Repository\ReservationpackvoyageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\PostRepository;
use App\Entity\Utilisateur;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\PayPalClient;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Psr\Log\LoggerInterface;

#[Route('/reservationpackvoyage')]
final class ReservationpackvoyageController extends AbstractController
{
    #[Route(name: 'app_reservationpackvoyage_index', methods: ['GET'])]
    public function index(ReservationpackvoyageRepository $reservationpackvoyageRepository): Response
    {
        return $this->render('reservationpackvoyage/index.html.twig', [
            'reservationpackvoyages' => $reservationpackvoyageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reservationpackvoyage_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationpackvoyage = new Reservationpackvoyage();
        $form = $this->createForm(ReservationpackvoyageType::class, $reservationpackvoyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $this->getUser();

            if ($utilisateur === null) {
                throw $this->createAccessDeniedException("Vous devez être connecté pour faire une réservation.");
            }

            $reservationpackvoyage->setUtilisateur($utilisateur);

            $entityManager->persist($reservationpackvoyage);
            $entityManager->flush();

            return $this->redirectToRoute('app_reservationpackvoyage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservationpackvoyage/new.html.twig', [
            'reservationpackvoyage' => $reservationpackvoyage,
            'form' => $form,
        ]);
    }

    #[Route('/{id_reservation}', name: 'app_reservationpackvoyage_show', methods: ['GET'])]
    public function show(Reservationpackvoyage $reservationpackvoyage): Response
    {
        return $this->render('reservationpackvoyage/show.html.twig', [
            'reservationpackvoyage' => $reservationpackvoyage,
        ]);
    }

    #[Route('/{id_reservation}/edit', name: 'app_reservationpackvoyage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservationpackvoyage $reservationpackvoyage, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationpackvoyageType::class, $reservationpackvoyage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservationpackvoyage_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservationpackvoyage/edit.html.twig', [
            'reservationpackvoyage' => $reservationpackvoyage,
            'form' => $form,
        ]);
    }

    #[Route('/{id_reservation}', name: 'app_reservationpackvoyage_delete', methods: ['POST'])]
    public function delete(Request $request, Reservationpackvoyage $reservationpackvoyage, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservationpackvoyage->getIdReservation(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservationpackvoyage);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservationpackvoyage_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/reserver/post/{id_post}', name: 'reservationpackvoyage_from_post', methods: ['POST'])]
public function reserveFromPost(
    int $id_post,
    Request $request,
    EntityManagerInterface $em,
    PostRepository $postRepo
): Response {
    $post = $postRepo->find($id_post);

    if (!$post || !$post->getPackvoyage()) {
        return new JsonResponse(['error' => 'Post ou pack introuvable.'], 404);
    }

    $user = $this->getUser();
    if (!$user) {
        return new JsonResponse(['error' => 'Authentification requise.'], 401);
    }

    if (in_array('ROLE_ADMIN', $user->getRoles())) {
        return new JsonResponse(['error' => 'Les administrateurs ne peuvent pas réserver.'], 403);
    }

    $nbPlaces = (int) $request->request->get('places');
    if ($nbPlaces <= 0) {
        return new JsonResponse(['error' => 'Le nombre de places doit être supérieur à 0.'], 400);
    }

    $pack = $post->getPackvoyage();
    if ($nbPlaces > $pack->getNombrePlacesRestants()) {
        return new JsonResponse(['error' => 'Pas assez de places disponibles.'], 400);
    }

    $reservation = new Reservationpackvoyage();
    $reservation->setUtilisateur($user);
    $reservation->setDateReservation(new \DateTime());
    $reservation->setStatut('en_attente');
    $reservation->setStatutPaiement('non payée');
    $reservation->setPlacesDemandees($nbPlaces);
    $reservation->setMontantTotal($pack->getPrixTotal() * $nbPlaces);
    $reservation->setPackVoyage($pack);
    $reservation->setPostId($post->getIdPost());

    // ✅ Nouvelle ligne ajoutée ici :
    $reservation->setNomPackArchive($pack->getNomPack());

    $pack->setNombrePlacesRestants($pack->getNombrePlacesRestants() - $nbPlaces);

    $em->persist($reservation);
    $em->flush();

    return new JsonResponse([
        'success' => 'Réservation enregistrée avec succès !',
        'reservationId' => $reservation->getIdReservation(),
        'statutPaiement' => $reservation->getStatutPaiement()
    ]);
}


    #[Route('/payer/{idReservation}', name: 'reservationpackvoyage_payer', methods: ['GET'])]
    public function payer(
        int $idReservation,
        ReservationpackvoyageRepository $reservationRepo,
        PayPalClient $paypalClient
    ): Response {
        $reservation = $reservationRepo->find($idReservation);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }

        if ($reservation->getStatutPaiement() === 'payée') {
            $this->addFlash('info', 'Cette réservation est déjà payée.');
            return $this->redirectToRoute('app_reservationpackvoyage_show', ['id_reservation' => $idReservation]);
        }

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [[
                "amount" => [
                    "currency_code" => 'USD', // PAS 'TND' ni 'USA'
                    "value" => number_format($reservation->getMontantTotal(), 2, '.', '')
                ]
            ]],
            "application_context" => [
                "return_url" => $this->generateUrl('reservationpackvoyage_paiement_success', ['idReservation' => $idReservation], 0),
                "cancel_url" => $this->generateUrl('reservationpackvoyage_paiement_cancel', [], 0),
            ]
        ];

        $response = $paypalClient->getClient()->execute($request);

        foreach ($response->result->links as $link) {
            if ($link->rel === 'approve') {
                return new RedirectResponse($link->href);
            }
        }

        throw new \Exception('Impossible de générer le lien de paiement.');
    }
   
    
    #[Route('/paiement/success/{idReservation}', name: 'reservationpackvoyage_paiement_success')]
    public function success(
        int $idReservation,
        Request $request,
        PayPalClient $paypalClient,
        EntityManagerInterface $em,
        ReservationpackvoyageRepository $reservationRepo,
        LoggerInterface $logger,
        \Symfony\Component\Mailer\MailerInterface $mailer,
        \Twig\Environment $twig,
        \App\Repository\PostRepository $postRepo // ✅ ajouté ici
    ): Response {
        $reservation = $reservationRepo->find($idReservation);
    
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation introuvable.');
        }
    
        $token = $request->query->get('token');
        if (!$token) {
            throw $this->createNotFoundException('Token manquant.');
        }
    
        try {
            $capture = new OrdersCaptureRequest($token);
            $capture->prefer('return=representation');
            $paypalClient->getClient()->execute($capture);
    
            $reservation->setStatut('CONFIRMEE');
            $reservation->setStatutPaiement('PAYE');
            $em->flush();
    
            $logger->info("[PayPal] Paiement réussi pour la réservation #{$reservation->getIdReservation()}.");
    
            // ✅ Récupérer le post à partir de l'id
            $post = $postRepo->find($reservation->getPostId());
    
            // ✅ ENVOI EMAIL via Gmail
            $user = $reservation->getUtilisateur();
            if (!$user) {
                $logger->error("[Mail] Aucun utilisateur lié à la réservation.");
            } elseif (!method_exists($user, 'getEmail')) {
                $logger->error("[Mail] La méthode getEmail() est manquante sur l'utilisateur.");
            } elseif (!$user->getEmail()) {
                $logger->error("[Mail] L'email de l'utilisateur est vide.");
            } else {
                $logger->info("[Mail] Envoi prévu à : {$user->getEmail()}");
    
                try {
                    $email = (new \Symfony\Component\Mime\Email())
                        ->from('abidiaymen6163@gmail.com')
                        ->to($user->getEmail())
                        ->subject('Confirmation de votre réservation')
                        ->html($twig->render('emails/reservation_confirmation.html.twig', [
                            'reservation' => $reservation,
                            'post' => $post // ✅ important
                        ]));
    
                    $mailer->send($email);
                    $logger->info("[Mail] Email envoyé avec succès !");
                } catch (\Throwable $mailException) {
                    $logger->error("[Mail] Échec de l'envoi : " . $mailException->getMessage());
                }
            }
    
            $this->addFlash('success', '✅ Paiement effectué avec succès.');
            return $this->redirectToRoute('client_mes_reservations');
    
        } catch (\Throwable $e) {
            $logger->error('[PayPal] Erreur : ' . $e->getMessage());
            $this->addFlash('error', 'Erreur lors du paiement.');
            return $this->redirectToRoute('client_mes_reservations');
        }
    }
    
    
    
    

    
    


    #[Route('/paiement/cancel', name: 'reservationpackvoyage_paiement_cancel')]
    public function cancel(): Response
    {
        return $this->render('reservationpackvoyage/paiement_cancel.html.twig');
    }
}
