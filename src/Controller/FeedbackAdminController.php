<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Service\MailService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\FeedbackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/feedback')]
class FeedbackAdminController extends AbstractController
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    #[Route('/', name: 'admin_feedback_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $feedbacks = $em->getRepository(Feedback::class)->findAll();
        
        // Calcul des stats pour le camembert
        $stats = [
            'labels' => ['1 étoile', '2 étoiles', '3 étoiles', '4 étoiles', '5 étoiles'],
            'data' => [0, 0, 0, 0, 0] // Initialisation
        ];
        
        foreach ($feedbacks as $feedback) {
            $note = $feedback->getNote();
            if ($note >= 1 && $note <= 5) {
                $stats['data'][$note-1]++;
            }
        }
        
        // Calcul de la moyenne
        $average = count($feedbacks) > 0 
            ? array_sum(array_map(fn($f) => $f->getNote(), $feedbacks)) / count($feedbacks)
            : 0;

        return $this->render('admin/feedback.html.twig', [
            'feedbacks' => $feedbacks,
            'stats' => $stats,
            'average_rating' => $average,
            'searchTerm' => null
        ]);
    }

    #[Route('/search', name: 'admin_feedback_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $term = $request->query->get('q');
        
        $qb = $em->getRepository(Feedback::class)
                ->createQueryBuilder('f')
                ->leftJoin('f.utilisateur', 'u')
                ->addSelect('u')
                ->orderBy('f.date_feedback', 'DESC');

        if (!empty($term)) {
            $qb->where('f.commentaire LIKE :term')
               ->orWhere('u.prenom LIKE :term')
               ->orWhere('u.nom LIKE :term')
               ->orWhere('u.email LIKE :term')
               ->setParameter('term', '%'.$term.'%');
        }

        $feedbacks = $qb->getQuery()->getResult();

        // Calcul des stats même pour la recherche
        $stats = [
            'labels' => ['1 étoile', '2 étoiles', '3 étoiles', '4 étoiles', '5 étoiles'],
            'data' => [0, 0, 0, 0, 0]
        ];
        
        foreach ($feedbacks as $feedback) {
            $note = $feedback->getNote();
            if ($note >= 1 && $note <= 5) {
                $stats['data'][$note-1]++;
            }
        }
        
        $average = count($feedbacks) > 0 
            ? array_sum(array_map(fn($f) => $f->getNote(), $feedbacks)) / count($feedbacks)
            : 0;

        return $this->render('admin/feedback.html.twig', [
            'feedbacks' => $feedbacks,
            'stats' => $stats,
            'average_rating' => $average,
            'searchTerm' => $term
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_feedback_delete')]
    public function delete(Feedback $feedback, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $feedback->getUtilisateur();
        if ($utilisateur) {
            $this->mailService->sendFeedbackEmail(
                $utilisateur->getEmail(),
                $utilisateur->getPrenom(),
                $feedback->getCommentaire(),
                $feedback->getNote()
            );
        }

        $entityManager->remove($feedback);
        $entityManager->flush();

        $this->addFlash('success', 'Feedback supprimé avec succès');
        return $this->redirectToRoute('admin_feedback_list');
    }
}