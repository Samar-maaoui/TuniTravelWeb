<?php 
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class InfobipService
{
    private $client;
    private $apiKey;
    private $baseUrl;

    public function __construct(HttpClientInterface $client, string $infobipApiKey)
    {
        $this->client = $client;
        $this->apiKey = $infobipApiKey;
        $this->baseUrl = 'https://4ez1vn.api.infobip.com/email/2/send';
    }

    public function sendEmail(string $to, string $subject, string $message): bool
    {
        try {
            $response = $this->client->request('POST', $this->baseUrl, [
                'headers' => [
                    'Authorization' => 'App ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'from' => [
                        'emailAddress' => 'samarmaaoui215@gmail.com', // Obligatoire !
                        'fromName' => 'Ton Nom' // Peut être n'importe quoi

                    ],
                    'to' => [
                        ['emailAddress' => $to]
                    ],
                    'subject' => $subject,
                    'text' => $message,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }
        } catch (\Exception $e) {
            // Log error
            return false;
        }

        return false;
    }
}
