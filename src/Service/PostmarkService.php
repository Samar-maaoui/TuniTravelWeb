<?php
namespace App\Service;

use Postmark\PostmarkClient;

class PostmarkService
{
    private $client;
    private $senderEmail;

    public function __construct(string $postmarkToken, string $senderEmail)
    {
        $this->client = new PostmarkClient($postmarkToken);
        $this->senderEmail = $senderEmail;
    }

    public function sendEmail(string $to, string $subject, string $textBody): bool
    {
        try {
            $this->client->sendEmail(
                $this->senderEmail,
                $to,
                $subject,
                $textBody,
                null // HTML body optionnel
            );
            return true;
        } catch (\Exception $e) {
            // Log ou afficher l'erreur
            echo 'Erreur Postmark : ' . $e->getMessage();
            return false;
        }
    }
}
