<?php

namespace App\Controller;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HotelController extends AbstractController
{
    #[Route('/admin/hotels', name: 'admin_hotel_list', methods: ['GET'])]
public function list(Request $request, HotelRepository $hotelRepository): Response
{
    $search = $request->query->get('search');
    $hotels = $search ? $hotelRepository->findByNomLike($search) : $hotelRepository->findAll();

    return $this->render('hotel/list.html.twig', [
        'hotels' => $hotels,
        'search' => $search
    ]);
}

    #[Route('/admin/hotel/new', name: 'admin_hotel_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement de l'image
            $imageFile = $form->get('image_hotel')->getData();
            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                try {
                    // Déplacer le fichier téléchargé
                    $imageFile->move(
                        $this->getParameter('images_events'), // répertoire configuré
                        $newFilename
                    );
                    $hotel->setImageHotel($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image.');
                    return $this->render('hotel/add.html.twig', [
                        'form' => $form->createView(),
                        'hotel' => $hotel,
                    ]);
                }
            }

            $entityManager->persist($hotel);
            $entityManager->flush();
            $this->addFlash('success', 'L\'hôtel a été ajouté avec succès.');

            return $this->redirectToRoute('admin_hotel_list');
        }

        return $this->render('hotel/add.html.twig', [
            'form' => $form->createView(),
            'hotel' => $hotel,
        ]);
    }

    #[Route('/admin/hotel/{id_hotel}/edit', name: 'admin_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'L\'hôtel a été modifié avec succès.');

            return $this->redirectToRoute('admin_hotel_list');
        }

        return $this->render('hotel/edit.html.twig', [
            'form' => $form->createView(),
            'hotel' => $hotel,
        ]);
    }

    #[Route('/admin/hotel/{id_hotel}/delete', name: 'admin_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $hotel->getIdHotel(), $request->request->get('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
            $this->addFlash('success', 'L\'hôtel a été supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_hotel_list');
    }
    #[Route('/hotels', name: 'client_hotel_list', methods: ['GET'])]
    public function clientHotelList(HotelRepository $hotelRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $queryBuilder = $hotelRepository->createQueryBuilder('h');
    
        $query = $queryBuilder->getQuery();
        $query->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true); // <<< ajout important ici
    
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            2
        );
    
        return $this->render('hotel/afficher_hotels_client.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    
    
}