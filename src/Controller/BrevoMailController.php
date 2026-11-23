<?php
namespace App\Controller;

use App\Service\BrevoEmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BrevoMailController extends AbstractController
{
    #[Route('/send-test-email', name: 'send_test_email')]
    public function sendTestEmail(BrevoEmailService $BrevoEmailService): Response
    {
        try {
           /* $BrevoEmailService->sendEmail(
                'samarmaaoui215@gmail.com', // Destinataire
                'Test Email',               // Sujet
                '<h1>Hello!</h1><p>This is a test email from Brevo</p>', // HTML
                null,                      // From (optionnel)
                'Hello! This is a test email from Brevo' // Texte brut
            );*/

            $this->addFlash('success', 'Email sent successfully!');
        } catch (\RuntimeException $e) {
            $this->addFlash('error', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_dashboard');
    }
}
