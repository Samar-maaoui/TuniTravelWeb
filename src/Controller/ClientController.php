<?php

namespace App\Controller;

use App\Entity\Evenements;
use App\Entity\Excursion;
use App\Entity\Post;
use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Form\EditUtilisateurType;
use App\Form\LoginFormType;
use App\Repository\EvenementsRepository;
use App\Repository\ExcursionRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationEvenementRepository;
use App\Repository\VolRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Repository\ReservationpackvoyageRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\MailService;
use Knp\Component\Pager\PaginatorInterface; // à ajouter en haut !
use App\Service\RecaptchaService;
use Doctrine\ORM\Tools\Pagination\Paginator;

final class ClientController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index1(UtilisateurRepository $utilisateurRepository): Response
    {
        // Check if user is authenticated and has admin role
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_login');
        }

        $users = $utilisateurRepository->findAll();
        
        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'controller_name' => 'UserController'
        ]);
    }
    #[Route('/', name: 'app_client')]
    public function index(EvenementsRepository $evenementRepository, PostRepository $postRepository): Response
    {
        $user = $this->getUser();

        if ($user) {
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                return $this->redirectToRoute('app_admin');
            } else {
                return $this->redirectToRoute('app_dashboard');
            }
        }
        $evenements = $evenementRepository->findAll();
        $posts = $postRepository->findLatest(2);
        
        return $this->render('client/index.html.twig', [
            'evenements' => $evenements,
            'posts' => $posts,
        ]);
    }
   
    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(
        Request $request, // Ajouter Request pour accéder aux paramètres de l'URL
        EvenementsRepository $evenementRepository,
        PostRepository $postRepository,
        ExcursionRepository $excursionRepository,
        VolRepository $volRepository,
        HotelRepository $hotelRepository,
        PaginatorInterface $paginator,

    ): Response {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        // Récupérer les paramètres de l'URL
        $location = $request->query->get('location');
        $price = $request->query->get('price');
    
        // Filtrer les excursions selon les paramètres location et prix
        $queryBuilder = $excursionRepository->createQueryBuilder('e');
    
        // Si le paramètre location est présent, filtrer les excursions par lieu
        if ($location) {
            $queryBuilder->andWhere('e.lieu = :location')
                         ->setParameter('location', $location);
        }
    
        // Si le paramètre price est présent, filtrer les excursions par prix
        if ($price) {
            $queryBuilder->andWhere('e.prix <= :price')
                         ->setParameter('price', $price);
        }
    
        // Exécuter la requête pour récupérer les excursions filtrées
        $excursions = $queryBuilder->getQuery()->getResult();
    
        // Récupérer les autres données (événements, posts, hôtels, vols)
        $hotels = $hotelRepository->findAll();
        $evenements = $evenementRepository->findAll();
        $posts = $postRepository->findLatest(2);
        $vols = $volRepository->findAll();
$excursions = $excursionRepository->findAll();
    
     // Hôtels paginés
    $hotelsQuery = $hotelRepository->createQueryBuilder('h')
        ->orderBy('h.nom', 'ASC') // tu peux changer ASC -> DESC si tu veux
        ->getQuery();

    $hotels = $paginator->paginate(
        $hotelsQuery,
        $request->query->getInt('page', 1), // page actuelle
        4 // 4 hôtels par page par exemple
    );

    return $this->render('client/dashboard.html.twig', [
        'evenements' => $evenements,
        'posts' => $posts,
        'excursions' => $excursions,
        'vols' => $vols,
        'hotels' => $hotels, // hotels paginés
    ]);
}
    

    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        RecaptchaService $recaptchaService,
        MailService $mailService
    ): Response {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification reCAPTCHA facultative
            $captchaToken = $request->request->get('g-recaptcha-response');
            $isCaptchaValid = $captchaToken && $recaptchaService->isValid($captchaToken);
    
            if ($isCaptchaValid) {
                $utilisateur->setIsVerified(1);
            } else {
                $utilisateur->setIsVerified(0);
    
                // Envoi d'un mail d'information
                $mailService->sendAccountInvalidEmail(
                    $utilisateur->getEmail(),
                    $utilisateur->getNom() ?? 'Utilisateur'
                );
    
                // Message d'information non bloquant
                $this->addFlash('warning', 'Votre compte a été créé, mais il nécessite une vérification manuelle par un administrateur.');
            }
    
            // Hashage du mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $utilisateur,
                $utilisateur->getMotDePasse()
            );
            $utilisateur->setMotDePasse($hashedPassword);
    
            // Détection du type utilisateur
            $email = $utilisateur->getEmail();
            $type = str_contains($email, '@esprit') ? 'VIP' : 'Normal';
            $utilisateur->setTypeUtilisateur($type);
    
            // Gestion de la photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();
    
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $utilisateur->setPhoto($newFilename);
                } catch (FileException $e) {
                    // log or gérer l'erreur si besoin
                }
            }
    
            $utilisateur->setDateCreation(new \DateTime());
    
            $entityManager->persist($utilisateur);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
            'recaptcha_site_key' => $this->getParameter('recaptcha_site_key')
        ]);
    }
    
    
    
    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Si l'utilisateur est déjà connecté, redirigez-le
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }
    
        // Créez le formulaire
        $form = $this->createForm(LoginFormType::class);
        
        // Traitez la soumission du formulaire
        $form->handleRequest($request);
        
        // Obtenez l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();
    
        return $this->render('client/login.html.twig', [
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be empty - it will be intercepted by the logout key on your firewall
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->redirectToRoute('app_profile_edit');
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ): Response {
        $utilisateur = $this->getUser();
    
        if (!$utilisateur) {
            return $this->redirectToRoute('app_login');
        }
    
        $oldEmail = $utilisateur->getEmail();
    
        $form = $this->createForm(EditUtilisateurType::class, $utilisateur);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
         
            // 🔹 Déterminer le type d'utilisateur
            $email = $utilisateur->getEmail();
            $type = str_contains($email, '@esprit') ? 'VIP' : 'Normal';
            $utilisateur->setTypeUtilisateur($type);
    
            // 🔹 Gestion de la photo de profil
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();
    
                try {
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                    $utilisateur->setPhoto($newFilename);
                } catch (FileException $e) {
                    
                }
            }
    
            $entityManager->flush();
    
            // 🔹 Déconnexion si l'email a changé
            if ($oldEmail !== $utilisateur->getEmail()) {
                $tokenStorage->setToken(null);
                $requestStack->getSession()->invalidate();
    
               
                return $this->redirectToRoute('app_login');
            }
    
            return $this->redirectToRoute('app_profile');
        }
    
        return $this->render('client/profile.html.twig', [
            'form' => $form->createView(),
            'user' => $utilisateur,
        ]);
    }
    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function deleteProfile(
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tokenStorage->setToken(null);
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/evenement/{id}', name: 'client_evenements_show', methods: ['GET'])]
    public function showClient(Evenements $evenement = null): Response
    {
        if (!$evenement) {
            throw $this->createNotFoundException('Événement non trouvé');
        }
        
        return $this->render('evenements/showClient.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/change-language/{locale}', name: 'change_language')]
    public function changeLanguage(Request $request, $locale): Response
    {
        // Set the session locale
        $request->getSession()->set('_locale', $locale);

        // Redirect back to the page after language change
        return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
    }
    #[Route('/excursions/{id}', name: 'client_excursions_show', methods: ['GET'])]
    public function showExcursion(Excursion $excursion): Response
    { if (!$excursion) {
        throw $this->createNotFoundException('excursion non trouvée');
    }
        return $this->render('excursion/showClient.html.twig', [
            'excursion' => $excursion,
        ]);
    }


    #[Route('/post/{id_post}', name: 'client_post_show', methods: ['GET'])]
    public function showPost($id_post, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($id_post);
        
        if (!$post) {
            throw $this->createNotFoundException('Pack non trouvé');
        }
        
        return $this->render('post/showclient.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/clientpost', name: 'client_post_index', methods: ['GET'])]
    public function postIndex(PostRepository $postRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
    
        $posts = $postRepository->findAll();
        
        return $this->render('post/indexclient.html.twig', [
            'posts' => $posts,
        ]);
    }
    #[Route('/clientpostcards', name: 'client_post_cards', methods: ['GET'])]
    public function postCards(PostRepository $postRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $posts = $postRepository->findAll();

        return $this->render('post/indexclientcards.html.twig', [
            'posts' => $posts,
        ]);
    }
    #[Route('/post/details/{id}', name: 'client_post_details', methods: ['GET'])]
    public function postDetails($id, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Pack non trouvé');
        }

        return $this->render('post/_detailsmodal.html.twig', [
            'post' => $post,
        ]);
    }


    
    #[Route('/client_reservationevenement',name: 'client_reservationevenement', methods: ['GET'])]
    public function indexreservation(ReservationEvenementRepository $reservationEvenementRepository): Response
    {
        $reservations = $reservationEvenementRepository->findAll();
    
        return $this->render('reservation_evenement/indexreservation.html.twig', [
            'reservation_evenements' => $reservations,
        ]);
    }
    #[Route('/mes-reservations', name: 'client_mes_reservations', methods: ['GET'])]
        public function mesReservations(ReservationpackvoyageRepository $repo): Response
        {
         $user = $this->getUser();

            if (!$user) {
                     return $this->redirectToRoute('app_login');
        }

    $reservations = $repo->findBy(['utilisateur' => $user]);

    return $this->render('client/mes_reservations.html.twig', [
        'reservations' => $reservations
    ]);
        }


        #[Route('/reservation', name: 'reservations', methods: ['GET'])]
        public function Reservations(): Response
        {
    

  
    return $this->render('mes_reservation.html.twig', [

    ]);
        }
}