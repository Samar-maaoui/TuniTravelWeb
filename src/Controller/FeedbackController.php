<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Form\FeedbackType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FeedbackRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'app_feedback')]
    public function feedback(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feedback = new Feedback();
        $utilisateur = $this->getUser();

        if ($utilisateur) {
            $feedback->setUtilisateur($utilisateur);
        }
        $feedback->setDateFeedback(new \DateTime());

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de la note depuis le formulaire
            $note = $form->get('note')->getData();
            
            // Assurez-vous que la note est un entier valide entre 1 et 5
            if ($note !== null && $note >= 1 && $note <= 5) {
                $feedback->setNote($note);
                
                try {
                    $entityManager->persist($feedback);
                    $entityManager->flush();
                    
                    return $this->redirectToRoute('app_feedback_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner une note entre 1 et 5 étoiles.');
            }
        }

        return $this->render('feedback/new.html.twig', [
            'form' => $form->createView(),
            'feedback' => $feedback
        ]);
    }
    
    #[Route('/feedback/supprimer/{id}', name: 'feedback_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function supprimerFeedback(Feedback $feedback, Request $request, EntityManagerInterface $em): RedirectResponse
    {
        if ($feedback->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete' . $feedback->getId(), $request->request->get('_token'))) {
            $em->remove($feedback);
            $em->flush();
        }

        return $this->redirectToRoute('app_feedback');
    }
    
    #[Route('/feedback/modifier/{id}', name: 'feedback_edit')]
    public function edit(Feedback $feedback, Request $request, EntityManagerInterface $em): Response
    {
        if ($feedback->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(FeedbackType::class, $feedback);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération de la note depuis le formulaire
            $note = $form->get('note')->getData();
            
            // Assurez-vous que la note est un entier valide entre 1 et 5
            if ($note !== null && $note >= 1 && $note <= 5) {
                $feedback->setNote($note);
                
                try {
                    $em->flush();
                    $this->addFlash('success', 'Feedback modifié avec succès.');
                    return $this->redirectToRoute('app_feedback_list');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la modification: ' . $e->getMessage());
                }
            } else {
                $this->addFlash('error', 'Veuillez sélectionner une note entre 1 et 5 étoiles.');
            }
        }

        return $this->render('feedback/edit.html.twig', [
            'form' => $form->createView(),
            'feedback' => $feedback
        ]);
    }
    
    #[Route('/new', name: 'app_feedback_new')]
    public function new(Request $request): Response
    {
        return $this->redirectToRoute('app_feedback');
    }

    #[Route('/list', name: 'app_feedback_list')]
    public function list(): Response
    {
        $feedbacks = $this->getUser()->getFeedbacks();
        
        return $this->render('feedback/list.html.twig', [
            'feedbacks' => $feedbacks,
        ]);
    }
}