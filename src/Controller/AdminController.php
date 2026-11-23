<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Service\IdentityVerificationService;

final class AdminController extends AbstractController
{
    #[Route('/admin/users/search', name: 'admin_user_search', methods: ['GET'])]
    public function search(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
    
        $query = strtolower($request->query->get('query', ''));
        $isVerified = $request->query->get('isVerified');
        $sort = $request->query->get('sort');
    
        $qb = $utilisateurRepository->createQueryBuilder('u');
    
        if (!empty($query)) {
            $qb->andWhere('LOWER(u.nom) LIKE :q OR LOWER(u.prenom) LIKE :q OR LOWER(u.email) LIKE :q OR LOWER(u.cin) LIKE :q OR LOWER(u.adresse) LIKE :q OR LOWER(u.telephone) LIKE :q OR LOWER(u.type_utilisateur) LIKE :q')
                ->setParameter('q', '%' . $query . '%');
        }
    
        if ($isVerified !== null && $isVerified !== '') {
            $qb->andWhere('u.is_verified = :verified')
                ->setParameter('verified', $isVerified);
        }
    
        switch ($sort) {
            case 'nom_asc':
                $qb->orderBy('u.nom', 'ASC');
                break;
            case 'nom_desc':
                $qb->orderBy('u.nom', 'DESC');
                break;
            case 'date_asc':
                $qb->orderBy('u.date_creation', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('u.date_creation', 'DESC');
                break;
            default:
                $qb->orderBy('u.id', 'DESC');
        }
    
        $users = $qb->getQuery()->getResult();
    
        // Ajoutez cette partie pour les statistiques
        $stats = [
            'verified' => $utilisateurRepository->count(['is_verified' => true]),
            'unverified' => $utilisateurRepository->count(['is_verified' => false]),
            'total' => $utilisateurRepository->count([]),
            'admins' => $utilisateurRepository->count(['type_utilisateur' => 'Admin']),
            'vips' => $utilisateurRepository->count(['type_utilisateur' => 'VIP']),
            'normals' => $utilisateurRepository->count(['type_utilisateur' => 'Normal'])
        ];
    
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'stats' => $stats, // N'oubliez pas d'ajouter cette ligne
            'search_query' => $query,
            'is_verified' => $isVerified,
            'sort' => $sort,
        ]);
    }
    
    #[Route('/admin/users/stats', name: 'admin_users_stats')]
public function stats(UtilisateurRepository $utilisateurRepository): Response
{
    if (!$this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_login');
    }

    $stats = [
        'verified' => $utilisateurRepository->count(['is_verified' => true]),
        'unverified' => $utilisateurRepository->count(['is_verified' => false]),
        'total' => $utilisateurRepository->count([]),
        'admins' => $utilisateurRepository->count(['type_utilisateur' => 'Admin']),
        'clients' => $utilisateurRepository->count(['type_utilisateur' => 'Client'])
    ];

    return $this->render('admin/index.html.twig', [
        'users' => $utilisateurRepository->findAll(),
        'stats' => $stats,
        'search_query' => '',
        'is_verified' => '',
        'sort' => ''
    ]);
}
#[Route('/admin', name: 'admin_users_list')]
public function index(UtilisateurRepository $utilisateurRepository): Response
{
    if (!$this->isGranted('ROLE_ADMIN')) {
        return $this->redirectToRoute('app_login');
    }

    $stats = [
        'verified' => $utilisateurRepository->count(['is_verified' => true]),
        'unverified' => $utilisateurRepository->count(['is_verified' => false]),
        'total' => $utilisateurRepository->count([]),
        'admins' => $utilisateurRepository->count(['type_utilisateur' => 'Admin']),
        'vips' => $utilisateurRepository->count(['type_utilisateur' => 'VIP']),
        'normals' => $utilisateurRepository->count(['type_utilisateur' => 'Normal'])
    ];

    return $this->render('admin/index.html.twig', [
        'users' => $utilisateurRepository->findAll(),
        'stats' => $stats,
        'search_query' => '',
        'is_verified' => '',
        'sort' => ''
    ]);
}
    

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(
        Utilisateur $utilisateur, 
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        try {
            $entityManager->remove($utilisateur);
            $entityManager->flush();
          
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting user.');
        }

        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/admin/users/{id}/confirm-delete', name: 'admin_user_confirm_delete', methods: ['GET'])]
    public function confirmDelete(Utilisateur $utilisateur): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('admin/delete_user.html.twig', [
            'utilisateur' => $utilisateur
        ]);
    }

    #[Route('/admin/users/{id}/validate', name: 'admin_user_validate', methods: ['POST'])]
    public function validate(
        Utilisateur $utilisateur,
        EntityManagerInterface $entityManager,
        IdentityVerificationService $verificationService
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }
    
        // Génère et sauvegarde le token AVANT l'envoi de l'email
        $token = bin2hex(random_bytes(32));
        $utilisateur->setVerificationToken($token);
        $utilisateur->setVerificationTokenCreatedAt(new \DateTime());
        $entityManager->flush(); // ⚠️ Sauvegarde en base
    
        // Envoie l'email seulement après la persistance
        if ($verificationService->sendVerificationRequest($utilisateur)) {
            $this->addFlash('success', 'Email envoyé.');
        } else {
            $this->addFlash('error', 'Échec de l\'envoi.');
        }
    
        return $this->redirectToRoute('admin_users_list');
    }
    
}