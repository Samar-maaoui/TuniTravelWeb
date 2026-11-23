<?php
namespace App\Controller;

use App\Repository\EvenementsRepository;
use App\Service\PaymeeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymeeController extends AbstractController
{
    #[Route('/payment/create/{id}', name: 'payment_create')]
    public function createPayment(
        int $id,
        EvenementsRepository $evenementRepository,
        PaymeeService $paymeeService
    ): Response {
        // Get event from database
        $event = $evenementRepository->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        try {
            // Create payment and get redirect URL
            $response = $paymeeService->createPayment(
                $event->getPrix(),                // amount (float)
                'test@example.com',               // email (string) 
                'Test',                           // firstName (string)
                'User',                           // lastName (string)
                '12345678',                       // phone (string|null)
                $event->getNom(),                 // eventName (string)
                'ORDER_' . uniqid()               // orderId (string) - The missing 7th parameter
            );
            // Redirect to Paymee's payment page
            return $this->redirect($response['payment_url']);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Payment failed: '.$e->getMessage());
            return $this->redirectToRoute('event_show', ['id' => $id]);
        }
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig', [
            'message' => 'Payment successful!'
        ]);
    }

    #[Route('/payment/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig', [
            'message' => 'Payment canceled'
        ]);
    }

    #[Route('/api/payment/webhook', name: 'payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        // Process payment status update
        return $this->json(['status' => 'success']);
    }
}