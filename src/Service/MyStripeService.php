<?php
namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;

class MyStripeService
{
    public function __construct(private string $stripeSecretKey)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    public function createPaymentIntent(int $amount): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'eur',
            'payment_method_types' => ['card'],
        ]);
    }
}
