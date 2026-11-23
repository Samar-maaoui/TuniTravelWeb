<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\PackvoyageRepository;


#[Route('/admin/post')]
final class PostController extends AbstractController
{
    #[Route(name: 'admin_post_index', methods: ['GET'])]
    public function index(Request $request, PostRepository $repo): Response
    {
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'titre');
        $direction = strtoupper($request->query->get('direction', 'ASC'));
    
        if (!$search && !$sort) {
            $posts = $repo->findAll();
        } else {
            $posts = $repo->searchAndSort($search, $sort ?? 'titre', $direction);
        }
    
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }
    
    #[Route('/new', name: 'admin_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $packvoyage = $post->getPackvoyage();
            if ($packvoyage) {
                $post->setImageUrl($packvoyage->getImageUrl());
            }

            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('admin_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{titre}/show', name: 'admin_post_show', methods: ['GET'])]
    public function show(string $titre, PostRepository $postRepository): Response
    {
        $post = $postRepository->findOneBy(['titre' => urldecode($titre)]);
    
        if (!$post) {
            throw $this->createNotFoundException('Post introuvable.');
        }
    
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }
    
    #[Route('/{titre}/edit', name: 'admin_post_edit', methods: ['GET', 'POST'])]
    public function edit(string $titre, Request $request, EntityManagerInterface $entityManager, PostRepository $repo): Response
    {
        $post = $repo->findOneBy(['titre' => urldecode($titre)]);
    
        if (!$post) {
            throw $this->createNotFoundException('Post introuvable.');
        }
    
        // ⛔ Bloque la date de publication en mode édition
        $form = $this->createForm(PostType::class, $post, [
            'is_edit' => true, // ✅ on passe l'option
        ]);
        
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('admin_post_index');
        }
    
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }
    
    
    #[Route('/{id_post}/delete', name: 'admin_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getIdPost(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_post_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/auto-description/{id}', name: 'admin_post_auto_description', methods: ['GET'])]
public function autoDescription(PackvoyageRepository $repo, int $id): JsonResponse
{
    $pack = $repo->find($id);
    if (!$pack) {
        return new JsonResponse(['description' => ''], 404);
    }

    $description = sprintf(
        "🛫 Vol : %s - %s\n🏨 Hôtel : %s\n🎒 Excursion : %s\n📝 Détails : %s\n💰 Prix : %s€\n📅 %d place(s) disponible(s).",
        $pack->getVol()?->getNumeroVol(),
        $pack->getVol()?->getCompagnie(),
        $pack->getHotel()?->getNom(),
        $pack->getExcursion()?->getNom(),
        $pack->getDetails(),
        $pack->getPrixTotal(),
        $pack->getNombrePlacesRestants()
    );

    return new JsonResponse(['description' => $description]);
}
}
