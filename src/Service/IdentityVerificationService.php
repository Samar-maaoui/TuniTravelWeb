<?php

namespace App\Service;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\Utilisateur;
use App\Service\MailService;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class IdentityVerificationService
{
    private $mailService;
    private $router;
    private $httpClient;
    private $facePlusPlusApiKey;
    private $facePlusPlusApiSecret;

    public function __construct(
        MailService $mailService,
        UrlGeneratorInterface $router,
        HttpClientInterface $httpClient,
        string $facePlusPlusApiKey,
        string $facePlusPlusApiSecret
    ) {
        $this->mailService = $mailService;
        $this->router = $router;
        $this->httpClient = $httpClient;
        $this->facePlusPlusApiKey = $facePlusPlusApiKey;
        $this->facePlusPlusApiSecret = $facePlusPlusApiSecret;
    }

    public function sendVerificationRequest(Utilisateur $user): bool
    {
       

        $verificationLink = $this->router->generate('verify_identity', 
        ['token' => $user->getVerificationToken()],
        UrlGeneratorInterface::ABSOLUTE_URL
    );
        
        $subject = "Vérification d'identité ITINIREVES";
        $body = $this->getEmailTemplate($user->getNom(), $verificationLink);
        
        return $this->mailService->sendEmail($user->getEmail(), $subject, $body);
    }

    public function verifyFaces(string $imagePath1, string $imagePath2): bool
    {
        $response = $this->httpClient->request('POST', 'https://api-us.faceplusplus.com/facepp/v3/compare', [
            'body' => [
                'api_key' => $this->facePlusPlusApiKey,
                'api_secret' => $this->facePlusPlusApiSecret,
                'image_file1' => fopen($imagePath1, 'r'),
                'image_file2' => fopen($imagePath2, 'r'),
            ],
        ]);

        $data = $response->toArray(false); // false pour éviter l'exception automatique

        if (isset($data['confidence']) && $data['confidence'] >= 75) {
            return true;
        }

        return false;
    }

    private function getEmailTemplate(string $nom, string $verificationLink): string 
    {
        return "
            <html>
                <body>
                    <h2>Vérification d'identité ITINIREVES</h2>
                    <p>Bonjour {$nom},</p>
                    <p>Pour vérifier votre identité, veuillez suivre ces étapes :</p>
                    <ol>
                        <li>Cliquez sur le lien ci-dessous</li>
                        <li>Prenez une photo de votre visage avec votre webcam</li>
                        <li>Notre système d'IA vérifiera votre identité</li>
                    </ol>
                    <a href='{$verificationLink}'>Vérifier mon identité</a>
                    <p>Ce lien expire dans 24 heures.</p>
                </body>
            </html>
        ";
    }
}
