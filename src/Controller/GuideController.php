<?php

namespace App\Controller;
use App\Entity\Guide;
use App\Form\GuideType;
use App\Repository\GuideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;


#[Route('/guide')]
final class GuideController extends AbstractController{
#[Route('/guides',name:'app_guide_index', methods: ['GET'])]
    public function index(GuideRepository $guideRepository): Response
    {
        return $this->render('guide/index.html.twig', [
            'guides' => $guideRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_guide_new', methods: ['GET', 'POST'])]
   /* public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $guide = new Guide();
        $form = $this->createForm(GuideType::class, $guide);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($guide);
            $entityManager->flush();

            return $this->redirectToRoute('app_guide_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('guide/new.html.twig', [
            'guide' => $guide,
            'form' => $form,
        ]);
    }*/

    #[Route('/new', name: 'app_guide_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $guide = new Guide();
        $form = $this->createForm(GuideType::class, $guide);
        $form->handleRequest($request);
    
        // Récupération des langues même si le formulaire n'est pas encore valide
        $languesArray = $form->get('langues')->getData();
    
        if ($form->isSubmitted() && empty($languesArray)) {
            $form->get('langues')->addError(new \Symfony\Component\Form\FormError('Veuillez sélectionner au moins une langue.'));
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $nom = $guide->getNom();
                $prenom = $guide->getPrenom();
                $safeName = $slugger->slug($prenom . '-' . $nom);
                $newFilename = $safeName . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('guides_directory'),
                        $newFilename
                    );
                    $guide->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                    return $this->render('guide/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }
    
            // Gestion des langues
            $guide->setLangues(implode(', ', $languesArray));
    
            $entityManager->persist($guide);
            $entityManager->flush();
    
            $this->addFlash('success', 'Guide ajouté avec succès.');
            return $this->redirectToRoute('app_guide_index');
        }
    
        return $this->render('guide/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

   /* 

    

    #[Route('/{id_guide}', name: 'app_guide_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['id_guide' => 'id_guide'])] Guide $guide): Response
    {
        return $this->render('guide/show.html.twig', [
            'guide' => $guide,
        ]);
    }*/
    #[Route('/{id_guide}/edit', name: 'app_guide_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, #[MapEntity(mapping: ['id_guide' => 'id_guide'])] Guide $guide, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(GuideType::class, $guide);
    
        // 💡 Convertir la chaîne de langues (stockée comme string) en array pour pré-remplir le champ du formulaire
        $languesArray = explode(', ', $guide->getLangues());
        $form->get('langues')->setData($languesArray);
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // 🔁 Récupérer les langues sélectionnées (array) et les convertir en string
            $selectedLangues = $form->get('langues')->getData();
            $guide->setLangues(implode(', ', $selectedLangues));
                $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $safeName = $slugger->slug($guide->getPrenom() . '-' . $guide->getNom());
                $newFilename = $safeName . '.' . $imageFile->guessExtension();
    
                try {
                    $imageFile->move(
                        $this->getParameter('guides_directory'),
                        $newFilename
                    );
                    $guide->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l’upload de l’image.');
                }
            }
    
            $entityManager->flush();
            $this->addFlash('success', 'Guide modifié avec succès.');
    
            return $this->redirectToRoute('app_guide_index');
        }
    
        return $this->render('guide/edit.html.twig', [
            'guide' => $guide,
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/{id_guide}', name: 'app_guide_delete', methods: ['POST'])]
    public function delete(Request $request, #[MapEntity(mapping: ['id_guide' => 'id_guide'])] Guide $guide, EntityManagerInterface $entityManager): Response
   
    {
        if ($this->isCsrfTokenValid('delete' . $guide->getIdGuide(), $request->request->get('_token'))) {
            $entityManager->remove($guide);
            $entityManager->flush();
            $this->addFlash('success', 'Le guide a été supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Le token CSRF est invalide.');
        }
    
        return $this->redirectToRoute('app_guide_index', [], Response::HTTP_SEE_OTHER);
    }
    




}
