<?php

namespace App\Controller;

use App\Service\FaceVerificationService;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailService; // 👈 AJOUTER EN HAUT

class SecurityController extends AbstractController
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService; // 👈 on l’injecte
    }
    #[Route('/verify', name: 'verify_faces')]
    public function verify(Request $request, FaceVerificationService $faceVerificationService): Response
    {
        dd('Je suis bien dans verifyIdentity');
        // Exemple : chemins des fichiers images pour test
        $image1 = '/chemin/vers/image1.jpg';
        $image2 = '/chemin/vers/image2.jpg';

        $isVerified = $faceVerificationService->verifyFaces($image1, $image2);

        if ($isVerified) {
            return new Response('Les visages correspondent.');
        } else {
            return new Response('Les visages ne correspondent pas.');
        }
    }
    #[Route('/verify-identity/{token}', name: 'verify_identity')]
    public function verifyIdentity(
        string $token,
        Request $request,
        EntityManagerInterface $entityManager,
        FaceVerificationService $faceVerificationService,
        MailService $mailService
    ): Response {
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->findOneBy([
            'verification_token' => $token
        ]);
    
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }
    
        $tokenCreatedAt = $utilisateur->getVerificationTokenCreatedAt();
        if (!$tokenCreatedAt || $tokenCreatedAt->diff(new \DateTime())->days >= 1) {
            return $this->redirectToRoute('app_login');
        }
    
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('webcam_photo');
            if ($uploadedFile) {
                $destinationPhotoAi = $this->getParameter('kernel.project_dir') . '/public/uploads/photoai/';
                $extension = $uploadedFile->guessExtension() ?? 'jpg';
                $filename = uniqid() . '.' . $extension;
                $uploadedFile->move($destinationPhotoAi, $filename);
    
                $destinationPhotos = $this->getParameter('kernel.project_dir') . '/public/uploads/photos/';
                $originalPhotoPath = $destinationPhotos . $utilisateur->getPhoto();
                $newPhotoPath = $destinationPhotoAi . $filename;
    
                $data = $faceVerificationService->verifyFaces($originalPhotoPath, $newPhotoPath);
    
                $confidence = $data['confidence'] ?? 0;
                $pourcentage = number_format($confidence, 2);
    
                if ($confidence >= 75) {
                    $utilisateur->setIsVerified(true);
                    $utilisateur->setVerificationToken(null);
                    $utilisateur->setVerificationTokenCreatedAt(null);
                    $entityManager->flush();
                    $mailService->sendAccountVerifiedEmail($utilisateur->getEmail(), $utilisateur->getNom());
    
                    return $this->render('security/verification_success.html.twig', [
                        'utilisateur' => $utilisateur,
                        'pourcentage' => $pourcentage
                    ]);
                } else {
                    return $this->render('security/verification_failed.html.twig', [
                        'utilisateur' => $utilisateur,
                        'pourcentage' => $pourcentage,
                        'token' => $token, // 🔥 pour refaire une tentative

                    ]);
                }
            }
        }
    
        return $this->render('security/verify_identity.html.twig', [
            'token' => $token
        ]);
    }
    

}
