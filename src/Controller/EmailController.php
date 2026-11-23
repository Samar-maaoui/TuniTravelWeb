<?php

namespace App\Controller;

use App\Service\PostmarkService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends AbstractController
{
    #[Route('/send-email', name: 'send_email_postmark', methods: ['POST'])]
    public function sendPostmark(Request $request, PostmarkService $postmarkService): Response
    {
        // On récupère l'email de l'utilisateur depuis un formulaire (POST)
        $userEmail = $request->request->get('email');

        if (!$userEmail) {
            return new Response('❌ Aucun email fourni.', 400);
        }

        // Envoi de l'e-mail via Postmark
        $success = $postmarkService->sendEmail(
            $userEmail,
            'Test Email via Postmark',
            'Ceci est un test d\'envoi d\'email via l\'API Postmark.'
        );

        // Résultat
        if ($success) {
            return new Response('✅ Email envoyé à ' . $userEmail);
        } else {
            return new Response('❌ Erreur lors de l\'envoi de l\'email à ' . $userEmail);
        }
    }
}
