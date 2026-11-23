<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ResetPasswordRequestType;
use App\Form\ResetPasswordType;
use App\Repository\UtilisateurRepository;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ResetPasswordController extends AbstractController
{
    private $twilioService;
    private $entityManager;
    private $utilisateurRepository;

    public function __construct(
        TwilioService $twilioService,
        EntityManagerInterface $entityManager,
        UtilisateurRepository $utilisateurRepository
    ) {
        $this->twilioService = $twilioService;
        $this->entityManager = $entityManager;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, SessionInterface $session): Response
    {
        $form = $this->createForm(ResetPasswordRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->utilisateurRepository->findOneBy(['email' => $data['email']]);

            if (!$user) {
                $this->addFlash('danger', 'Cet email n\'existe pas dans notre système.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Check if phone number exists
            if (!$user->getTelephone()) {
                $this->addFlash('danger', 'Aucun numéro de téléphone associé à ce compte.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Generate a random 6-digit code
            $code = sprintf('%06d', mt_rand(1, 999999));
            $formattedPhone = $this->formatPhoneNumber(56791908);

            // Save the code and expiration time in the session
            $session->set('reset_code', $code);
            $session->set('reset_code_expires', time() + 3600); // Code expires in 1 hour
            $session->set('reset_email', $user->getEmail());

            try {
                // Send the code via WhatsApp
                $message = "Votre code de réinitialisation pour ITINIREVES est: $code. Ce code est valable pendant 1 heure.";
                $this->twilioService->sendWhatsAppMessage($formattedPhone, $message);

                $this->addFlash('success', 'Un code de réinitialisation a été envoyé à votre numéro WhatsApp.');
                return $this->redirectToRoute('app_verify_reset_code');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de l\'envoi du message: ' . $e->getMessage());
                return $this->redirectToRoute('app_forgot_password');
            }
        }

        return $this->render('client/reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/verify-reset-code', name: 'app_verify_reset_code')]
    public function verifyResetCode(Request $request, SessionInterface $session): Response
    {
        // Check if there's a reset code in the session
        if (!$session->has('reset_code') || !$session->has('reset_code_expires') || !$session->has('reset_email')) {
            $this->addFlash('danger', 'Aucune demande de réinitialisation en cours.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Check if the code has expired
        if (time() > $session->get('reset_code_expires')) {
            $session->remove('reset_code');
            $session->remove('reset_code_expires');
            $session->remove('reset_email');
            
            $this->addFlash('danger', 'Le code de réinitialisation a expiré. Veuillez recommencer.');
            return $this->redirectToRoute('app_forgot_password');
        }

        if ($request->isMethod('POST')) {
            $submittedCode = $request->request->get('code');
            $savedCode = $session->get('reset_code');

            if ($submittedCode === $savedCode) {
                // Code is valid, allow password reset
                $session->set('reset_code_verified', true);
                return $this->redirectToRoute('app_reset_password');
            } else {
                $this->addFlash('danger', 'Code invalide. Veuillez réessayer.');
            }
        }

        return $this->render('client/reset_password/verify_code.html.twig');
    }

    #[Route('/reset-password', name: 'app_reset_password')]
    public function resetPassword(
        Request $request, 
        SessionInterface $session,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Check if the code has been verified
        if (!$session->has('reset_code_verified') || !$session->get('reset_code_verified')) {
            $this->addFlash('danger', 'Vous devez vérifier votre code avant de réinitialiser votre mot de passe.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $session->get('reset_email');
            $user = $this->utilisateurRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('danger', 'Utilisateur non trouvé.');
                return $this->redirectToRoute('app_login');
            }

            // Update the user's password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Clear all the reset session variables
            $session->remove('reset_code');
            $session->remove('reset_code_expires');
            $session->remove('reset_email');
            $session->remove('reset_code_verified');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('client/reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function formatPhoneNumber($phone)
    {
        // Convert to string if it's a float
        $phone = (string)$phone;
        
        // Check if it already has a country code
        if (substr($phone, 0, 1) === '+') {
            return $phone;
        }
        
        // Assume Tunisian number and add +216 country code
        if (strlen($phone) === 8) {
            return '+216' . $phone;
        }
        
        // Return the original number if we can't format it
        return $phone;
    }
}