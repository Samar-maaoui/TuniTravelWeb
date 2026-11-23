<?php

namespace App\Controller;

use App\Entity\Packvoyage;
use App\Form\PackvoyageType;
use App\Repository\PackvoyageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Configuration\Options;
use CMEN\GoogleChartsBundle\GoogleCharts\Data;use App\Repository\ReservationpackvoyageRepository;



#[Route('/packvoyage')]
final class PackvoyageController extends AbstractController
{
    #[Route('/', name: 'app_packvoyage_index', methods: ['GET'])]
public function index(Request $request, PackvoyageRepository $repo): Response
{
    $search = $request->query->get('search');
    $sort = $request->query->get('sort');
    $direction = strtoupper($request->query->get('direction', 'ASC'));

    // Si aucun tri ni recherche, on utilise findAll()
    if (!$search && !$sort) {
        $packvoyages = $repo->findAll();
    } else {
        $packvoyages = $repo->searchAndSort($search, $sort ?? 'nomPack', $direction);
    }

    return $this->render('packvoyage/index.html.twig', [
        'packvoyages' => $packvoyages,
        'search' => $search,
        'sort' => $sort,
        'direction' => $direction,
    ]);
}


#[Route('/new', name: 'app_packvoyage_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    SluggerInterface $slugger,
    Security $security,
    \App\Service\UnsplashService $unsplashService
): Response {
    $packvoyage = new Packvoyage();
    $form = $this->createForm(PackvoyageType::class, $packvoyage);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // 🔐 Récupération de l'utilisateur connecté
        $admin = $security->getUser();
        if ($admin) {
            $packvoyage->setAdmin($admin);
        } else {
            $this->addFlash('error', 'Impossible de récupérer l\'utilisateur connecté.');
            return $this->redirectToRoute('app_packvoyage_new');
        }

        // 📷 Aller chercher une image automatiquement avec Unsplash
        $unsplashImageUrl = $unsplashService->searchImage($packvoyage->getNomPack());

        if ($unsplashImageUrl) {
            $packvoyage->setImageUrl($unsplashImageUrl);
        } else {
            // Image par défaut si Unsplash ne trouve rien
            $packvoyage->setImageUrl('default.jpg');
        }

        // 💾 Enregistrement
        $entityManager->persist($packvoyage);
        $entityManager->flush();

        $this->addFlash('success', 'Pack ajouté avec succès avec une image automatique !');
        return $this->redirectToRoute('app_packvoyage_index');
    }

    return $this->render('packvoyage/new.html.twig', [
        'form' => $form->createView(),
    ]);
}



    #[Route('/show/{nomPack}', name: 'app_packvoyage_show', methods: ['GET'])]
public function show(string $nomPack, PackvoyageRepository $repo): Response
{
    // décodage correct de l’URL
    $nomPack = urldecode($nomPack);

    $packvoyage = $repo->findOneBy(['nomPack' => $nomPack]);

    if (!$packvoyage) {
        throw $this->createNotFoundException('Pack introuvable.');
    }

    return $this->render('packvoyage/show.html.twig', [
        'packvoyage' => $packvoyage,
    ]);
}

    

#[Route('/edit/{nomPack}', name: 'app_packvoyage_edit', methods: ['GET', 'POST'])]
public function edit(
    string $nomPack, 
    Request $request, 
    EntityManagerInterface $entityManager, 
    PackvoyageRepository $repo
): Response {
    $nomPack = urldecode($nomPack);
    $packvoyage = $repo->findOneBy(['nomPack' => $nomPack]);

    if (!$packvoyage) {
        throw $this->createNotFoundException('Pack introuvable.');
    }

    $form = $this->createForm(PackvoyageType::class, $packvoyage);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // ❌ NE TOUCHE PLUS À l'image (elle reste comme elle est)
        $entityManager->flush();

        $this->addFlash('success', 'Pack modifié avec succès.');
        return $this->redirectToRoute('app_packvoyage_index');
    }

    return $this->render('packvoyage/edit.html.twig', [
        'packvoyage' => $packvoyage,
        'form' => $form->createView(),
    ]);
}





    #[Route('/{id_pack}', name: 'app_packvoyage_delete', methods: ['POST'])]
    public function delete(Request $request, Packvoyage $packvoyage, EntityManagerInterface $entityManager): Response
    {
        // Vérification du token CSRF avant de supprimer l'entité
        if ($this->isCsrfTokenValid('delete' . $packvoyage->getIdPack(), $request->request->get('_token'))) {
            // Suppression de l'entité
            $entityManager->remove($packvoyage);
            $entityManager->flush();
        }
    
        // Redirection vers la liste des packvoyages après suppression
        return $this->redirectToRoute('app_packvoyage_index');
    }
    #[Route('/stats', name: 'app_packvoyage_stats', methods: ['GET'])]
    public function stats(ReservationpackvoyageRepository $reservationRepo, PackvoyageRepository $packRepo): Response
    {
        // 📊 1. Données camembert (packs)
        $reservations = $reservationRepo->findAll();
        $packCounts = [];
    
        foreach ($reservations as $reservation) {
            $pack = $reservation->getPackVoyage();
            $packId = $pack ? $pack->getIdPack() : null;
    
            if ($packId !== null) {
                $packCounts[$packId] = ($packCounts[$packId] ?? 0) + 1;
            } else {
                $packCounts['deleted'] = ($packCounts['deleted'] ?? 0) + 1;
            }
        }
    
        $data = [['Pack', 'Réservations']];
        foreach ($packCounts as $packId => $count) {
            if ($packId === 'deleted') {
                $deletedReservations = $reservationRepo->findBy(['packVoyage' => null]);
                foreach ($deletedReservations as $deletedReservation) {
                    $nomPack = $deletedReservation->getNomPackArchive() ?? 'Pack supprimé';
                    $data[] = [$nomPack, 1];
                }
            } else {
                $pack = $packRepo->find($packId);
                $nomPack = $pack ? $pack->getNomPack() : 'Pack supprimé (ID: ' . $packId . ')';
                $data[] = [$nomPack, $count];
            }
        }
    
        $chartDataJson = json_encode($data);
    
        // 📈 2. Données line chart (évolution mensuelle)
        $moisMap = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
                    7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];
    
        $reservationsParMois = $reservationRepo->countByMonth();
        $lineData = [['Mois', 'Réservations']];
        foreach ($reservationsParMois as $row) {
            $moisNom = $moisMap[(int) $row['mois']] ?? 'Inconnu';
            $lineData[] = [$moisNom, (int) $row['total']];
        }
    
        $lineChartDataJson = json_encode($lineData);
    
        // 👥 3. Données bar chart (top utilisateurs)
        $topUsers = $reservationRepo->topUtilisateurs();
        $barData = [['Utilisateur', 'Réservations']];
        $colorPalette = ['#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#3B3EAC', '#0099C6', '#DD4477', '#66AA00', '#B82E2E'];
        $barColors = [];
    
        foreach ($topUsers as $index => $user) {
            $barData[] = [$user['nom_complet'], (int) $user['total']];
            $barColors[] = $colorPalette[$index % count($colorPalette)];
        }
    
        $barChartDataJson = json_encode($barData);
        $barColorsJson = json_encode($barColors);
    
        return $this->render('packvoyage/stats.html.twig', [
            'chartDataJson' => $chartDataJson,
            'lineChartDataJson' => $lineChartDataJson,
            'barChartDataJson' => $barChartDataJson,
            'barColors' => $barColorsJson,
        ]);
    }
    
    }
    

    

    
