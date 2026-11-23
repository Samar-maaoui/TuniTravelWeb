<?php

namespace App\Controller;



use App\Entity\Evenements;
use App\Form\Evenements1Type;
use App\Repository\EvenementsRepository;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
#[Route('/evenements')]
final class EvenementsController extends AbstractController
{
    #[Route('/',name: 'app_evenements_index', methods: ['GET'])]
    public function index(Request $request, EvenementsRepository $evenementsRepository): Response
    {
       
        $search = $request->query->get('search');
        $type = $request->query->get('type');
        $lieu = $request->query->get('lieu');
    

        $evenements = $evenementsRepository->findByFilters($search, $type, $lieu);
        
 
        $adapter = new ArrayAdapter($evenements);
      
        $pagerFanta = new PagerFanta($adapter);
        $pagerFanta->setMaxPerPage(4); 
        $pagerFanta->setCurrentPage($request->query->getInt('page', 1)); 
        
        $lieux = $evenementsRepository->findDistinctLieux();
    
        return $this->render('evenements/index.html.twig', [
            'pagerFanta' => $pagerFanta,
            'lieux' => $lieux,
        ]);
    }

    #[Route('/new', name: 'app_evenements_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenements();
        $form = $this->createForm(Evenements1Type::class, $evenement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                
                try {
                    // Move the uploaded file to the directory where images are stored
                    $imageFile->move(
                        $this->getParameter('images_events'), // Make sure the directory is defined in services.yaml
                        $newFilename
                    );
    
                    // Set the image filename in the entity
                    $evenement->setImage($newFilename);
                } catch (FileException $e) {
                    // Handle errors during file upload (if needed)
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }
    
            // Persist the event entity with the image data
            $entityManager->persist($evenement);
            $entityManager->flush();
    
            // Redirect to the index page after saving
            return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('evenements/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_evenements_show', methods: ['GET'])]
    public function show(Evenements $evenement): Response
    {
        return $this->render('evenements/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_evenements_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenements $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Evenements1Type::class, $evenement);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
    
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move(
                        $this->getParameter('images_events'),
                        $newFilename
                    );
                    $evenement->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'upload de l\'image.');
                }
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('evenements/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}', name: 'app_evenements_delete', methods: ['POST'])]
    public function delete(Request $request, Evenements $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenements_index', [], Response::HTTP_SEE_OTHER);
    }

}
