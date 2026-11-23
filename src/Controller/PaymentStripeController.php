<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PaymentStripeController extends AbstractController
{
    #[Route('/checkout', name: 'stripe_checkout')]
    public function checkout(): Response
    {
        // Tu peux fixer le montant ici, ou le récupérer dynamiquement selon une excursion
        $montantTotal = 100.00; // Exemple : 100 AED

        // Clé secrète Stripe (tu peux aussi l'injecter via un service ou %env()%)
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        // Création de la session Stripe Checkout
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'aed',
                    'unit_amount' => (int)($montantTotal * 100), // toujours en centimes
                    'product_data' => [
                        'name' => 'Paiement excursion',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        // Redirige vers Stripe Checkout
        return $this->redirect($session->url, 303);
    }

    #[Route('/success', name: 'payment_success')]
    public function success(): Response
    {
        return new Response('<h2>Paiement réussi ! 🎉</h2>');
    }

    #[Route('/cancel', name: 'payment_cancel')]
    public function cancel(): Response
    {
        return new Response('<h2>Paiement annulé.</h2>');
    }
}
