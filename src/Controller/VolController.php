<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Vol;
use App\Form\VolType;
use App\Repository\VolRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/vol')]
final class VolController extends AbstractController
{
    #[Route(name: 'app_vol_index', methods: ['GET'])]
    public function index(VolRepository $volRepository): Response
    {
        return $this->render('vol/index.html.twig', [
            'vols' => $volRepository->findAll(),
        ]);
    }

    #[Route('/admin', name: 'app_vol_index1', methods: ['GET'])]
    public function index1(VolRepository $volRepository): Response
    {
        return $this->render('vol/index1.html.twig', [
            'vols' => $volRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vol_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vol = new Vol();
        $form = $this->createForm(VolType::class, $vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vol);
            $entityManager->flush();

            $this->addFlash('success', 'Le vol a été créé avec succès.');
            return $this->redirectToRoute('app_vol_index');
        }

        return $this->render('vol/new.html.twig', [
            'vol' => $vol,
            'form' => $form,
        ]);
    }

    #[Route('/{id_vol}', name: 'app_vol_show', methods: ['GET'])]
    public function show(Vol $vol): Response
    {
        return $this->render('vol/show.html.twig', [
            'vol' => $vol,
        ]);
    }

    #[Route('/{id_vol}/edit', name: 'app_vol_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vol $vol, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VolType::class, $vol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le vol a été modifié avec succès.');
            return $this->redirectToRoute('app_vol_index');
        }

        return $this->render('vol/edit.html.twig', [
            'vol' => $vol,
            'form' => $form,
        ]);
    }

    #[Route('/{id_vol}', name: 'app_vol_delete', methods: ['POST'])]
    public function delete(Request $request, Vol $vol, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vol->getIdVol(), $request->get('_token'))) {
            try {
                $entityManager->remove($vol);
                $entityManager->flush();
                $this->addFlash('success', 'Le vol a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du vol.');
                // Gérer l'erreur (par exemple, logger)
                return $this->redirectToRoute('app_vol_index'); // Ensure redirect on error
            }
        }

        return $this->redirectToRoute('app_vol_index');
    }
}