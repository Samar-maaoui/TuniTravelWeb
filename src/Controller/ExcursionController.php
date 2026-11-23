<?php

namespace App\Controller;
use App\Entity\Excursion;
use App\Form\ExcursionType;
use App\Repository\ExcursionRepository;
use App\Repository\ReservationExcursionRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/excursion')]
final class ExcursionController extends AbstractController{
 
    #[Route('/excursions', name: 'app_excursion_index', methods: ['GET'])]
    public function index(Request $request, ExcursionRepository $excursionRepository): Response
    {
        $location = $request->query->get('location');
        $price = $request->query->get('price');
    
        // J'appelle directement le repository avec mes filtres
        $excursions = $excursionRepository->findByFilters($location, $price);
    
        return $this->render('excursion/index.html.twig', [
            'excursions' => $excursions,
        ]);
    }
    
    
 
    #[Route('/new', name: 'app_excursion_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $excursion = new Excursion();
        $form = $this->createForm(ExcursionType::class, $excursion);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $nomExcursion = $excursion->getNom();
                $safeFilename = $slugger->slug($nomExcursion); // sécuriser le nom
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('excursions_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // gestion d'erreur si upload échoue
                    throw new \Exception("Erreur lors du téléchargement de l'image");
                }
    
                // Enregistre le nom du fichier dans l'entité
                $excursion->setImage($newFilename);
            }
    
            // Enregistre dans la base
            $entityManager->persist($excursion);
            $entityManager->flush();
            $this->addFlash('success', 'Excursion ajoutée avec succès !');

            return $this->redirectToRoute('app_excursion_index');
        }

        return $this->render('excursion/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/{id_excursion}', name: 'app_excursion_show', methods: ['GET'])]
    public function show(Excursion $excursion, 
                         ReservationExcursionRepository $reservationExcursionRepository,
                         ReservationRepository $reservationRepository): Response
    {
        // Récupérer toutes les réservations liées à cette excursion à partir de ReservationExcursion
        $reservationExcursions = $reservationExcursionRepository->createQueryBuilder('re')
            ->innerJoin('re.reservation', 'r')  // Jointure avec l'entité Reservation
            ->where('re.excursion = :excursion')  // Filtrer par l'ID de l'excursion
            ->setParameter('excursion', $excursion)  // Paramètre de l'excursion
            ->getQuery()
            ->getResult();
    
        // Maintenant, pour chaque réservation liée à cette excursion, récupérer les informations supplémentaires depuis Reservation
        $reservations = [];
        foreach ($reservationExcursions as $reservationExcursion) {
            // Utilisation du repository de Reservation pour récupérer les informations associées
            $reservation = $reservationRepository->find($reservationExcursion->getReservation()->getIdReservation());
    
            if ($reservation) {
                $reservations[] = [
                    'reservation' => $reservation,
                    'nbr_place_reservee' => $reservationExcursion->getNbrPlaceReservee(),  // Nombre de places réservées
                ];
            }
        }
    
        return $this->render('excursion/show.html.twig', [
            'excursion' => $excursion,
            'reservations' => $reservations,
        ]);
    }
    


    #[Route('/{id_excursion}/edit', name: 'app_excursion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Excursion $excursion, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ExcursionType::class, $excursion);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // 📸 Gestion de l’image si une nouvelle est uploadée
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $safeName = $slugger->slug($excursion->getNom());
                $newFilename = $safeName . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('excursions_directory'), 
                        $newFilename
                    );
                    $excursion->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                }
            }
    
            $entityManager->flush();
            $this->addFlash('success', 'Excursion modifiée avec succès !');
    
            return $this->redirectToRoute('app_excursion_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('excursion/edit.html.twig', [
            'excursion' => $excursion,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/{id_excursion}', name: 'app_excursion_delete', methods: ['POST'])]
    public function delete(Request $request, Excursion $excursion, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$excursion->getIdExcursion(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($excursion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_excursion_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/stats/reservations', name: 'stats_reservations')]
public function reservationsStats(ReservationExcursionRepository $repo): Response
{
    $stats = $repo->countReservationsByExcursion();

    $labels = [];
    $data = [];
    
    foreach ($stats as $stat) {
        $labels[] = $stat['excursion'];
        $data[]   = $stat['total'];
    }

    return $this->render('reservation_excursion\statistique.html.twig', [
        'labels' => json_encode($labels),
        'data' => json_encode($data),
    ]);
}

}
