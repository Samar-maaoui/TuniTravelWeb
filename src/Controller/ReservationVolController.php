<?php

namespace App\Controller;

use App\Entity\Reservationvol;
use App\Entity\Vol;
use App\Form\ReservationvolType;
use App\Repository\ReservationvolRepository;
use App\Repository\VolRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException; // Ajout

#[Route('/reservation/vol')]
final class ReservationVolController extends AbstractController
{
    #[Route(name: 'app_reservation_vol_index', methods: ['GET'])]
    public function index(ReservationvolRepository $reservationvolRepository): Response
    {
        $reservations = $reservationvolRepository->findAll();
        $barcodes = $this->generateBarcodes($reservations);
        $qrcodes = $this->generateQRCodes($reservations);

        return $this->render('reservation_vol/index.html.twig', [
            'reservationvols' => $reservations,
            'barcodes' => $barcodes,
            'qrcodes' => $qrcodes,
        ]);
    }

    #[Route('/reservation_vol_index1', name: 'app_reservation_vol_index1', methods: ['GET'])]
    public function index1(Request $request, ReservationvolRepository $reservationvolRepository, PaginatorInterface $paginator): Response
    {
        $reservationsQuery = $reservationvolRepository->createQueryBuilder('r')->getQuery();
        $reservations = $paginator->paginate(
            $reservationsQuery,
            $request->query->getInt('page', 1),
            5
        );

        $barcodes = $this->generateBarcodes($reservations->getItems());
        $qrcodes = $this->generateQRCodes($reservations->getItems());

        $allReservations = $reservationvolRepository->findAll();
        $stats = [
            'ÉCONOMIE' => 0,
            'AFFAIRES' => 0,
            'PREMIÈRE' => 0
        ];

        foreach ($allReservations as $reservation) {
            $classe = $reservation->getClasseService();
            if (isset($stats[$classe])) {
                $stats[$classe]++;
            }
        }

        return $this->render('reservation_vol/index1.html.twig', [
            'reservationvols' => $reservations,
            'stats' => $stats,
            'barcodes' => $barcodes,
            'qrcodes' => $qrcodes,
        ]);
    }

    #[Route('/new', name: 'app_reservation_vol_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reservationvol = new Reservationvol();
        $form = $this->createForm(ReservationvolType::class, $reservationvol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservationvol);
            $entityManager->flush();

            $this->addFlash('success', 'La réservation a été créée avec succès.'); // Ajout
            return $this->redirectToRoute('app_reservation_vol_index');
        }

        return $this->render('reservation_vol/new.html.twig', [
            'reservationvol' => $reservationvol,
            'form' => $form,
        ]);
    }

    #[Route('/{id_reservation}', name: 'app_reservation_vol_show', methods: ['GET'], requirements: ['id_reservation' => '\d+'])]
    public function show(Reservationvol $reservationvol): Response
    {
        return $this->render('reservation_vol/show.html.twig', [
            'reservationvol' => $reservationvol,
        ]);
    }

    #[Route('/{id_reservation}/edit', name: 'app_reservation_vol_edit', methods: ['GET', 'POST'], requirements: ['id_reservation' => '\d+'])]
    public function edit(Request $request, Reservationvol $reservationvol, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $reservationvol->getUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à modifier cette réservation.'); // Ajout
        }

        $form = $this->createForm(ReservationvolType::class, $reservationvol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La réservation a été modifiée avec succès.'); // Ajout
            return $this->redirectToRoute('app_reservation_vol_index');
        }

        return $this->render('reservation_vol/edit.html.twig', [
            'reservationvol' => $reservationvol,
            'form' => $form,
        ]);
    }

    #[Route('/{id_reservation}', name: 'app_reservation_vol_delete', methods: ['POST'], requirements: ['id_reservation' => '\d+'])]
    public function delete(Request $request, Reservationvol $reservationvol, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $reservationvol->getUtilisateur() && !$this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Vous n\'êtes pas autorisé à supprimer cette réservation.'); // Ajout
        }

        if ($this->isCsrfTokenValid('delete' . $reservationvol->getIdReservation(), $request->get('_token'))) {
            try {
                $entityManager->remove($reservationvol);
                $entityManager->flush();
                $this->addFlash('success', 'La réservation a été supprimée avec succès.'); // Ajout
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la réservation.'); // Ajout
                // Gérer l'erreur (par exemple, logger)
            }
        }

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_reservation_vol_index1');
        }

        return $this->redirectToRoute('app_reservation_vol_index');
    }

    #[Route('/reserver/{id_vol}', name: 'app_reservation_vol_from_vol', methods: ['GET'])]
    public function reserverVol(int $id_vol, Request $request, VolRepository $volRepository, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $vol = $volRepository->find($id_vol);

        if (!$vol) {
            throw $this->createNotFoundException('Vol non trouvé.');
        }

        $classe = $request->query->get('classe', 'ÉCONOMIE');

        $reservation = new Reservationvol();
        $reservation->setVol($vol);
        $reservation->setUtilisateur($user);
        $reservation->setClasseService($classe);

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été enregistrée.'); // Ajout
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/mes-reservations', name: 'app_reservation_vol_user', methods: ['GET'])]
    public function userReservations(Request $request, ReservationvolRepository $reservationvolRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $search = $request->query->get('search');
        $sort = $request->query->get('sort');

        $queryBuilder = $reservationvolRepository->createQueryBuilder('r')
            ->join('r.vol', 'v')
            ->where('r.utilisateur = :user')
            ->setParameter('user', $user);

        if ($search) {
            $queryBuilder
                ->andWhere('v.aero_depart LIKE :search OR v.aero_arrivee LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($sort === 'prix') {
            $queryBuilder->orderBy('v.prix', 'ASC');
        } elseif ($sort === 'date') {
            $queryBuilder->orderBy('v.date_depart', 'ASC');
        }

        $reservations = $queryBuilder->getQuery()->getResult();
        $barcodes = $this->generateBarcodes($reservations);
        $qrcodes = $this->generateQRCodes($reservations);

        return $this->render('reservation_vol/index.html.twig', [
            'reservationvols' => $reservations,
            'barcodes' => $barcodes,
            'qrcodes' => $qrcodes,
        ]);
    }

    #[Route('/reservationvols', name: 'app_reservationvol_index')]
    public function index2(): Response
    {
        return $this->redirectToRoute('app_reservation_vol_index1');
    }

    #[Route('/barcode/{id}', name: 'app_generate_barcode', methods: ['GET'])]
    public function generateBarcode(int $id, ReservationvolRepository $reservationvolRepository): Response
    {
        $reservation = $reservationvolRepository->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        return $this->redirectToRoute('app_reservation_vol_details', ['id_reservation' => $reservation->getIdReservation()]);
    }

    #[Route('/reservation/details/{id_reservation}', name: 'app_reservation_vol_details', methods: ['GET'], requirements: ['id_reservation' => '\d+'])]
    public function reservationDetails(Reservationvol $reservationvol): Response
    {
        return $this->render('reservation_vol/details.html.twig', [
            'reservationvol' => $reservationvol,
        ]);
    }

    #[Route('/reservation/pdf/{id_reservation}', name: 'app_reservation_vol_pdf', methods: ['GET'])]
    public function generatePdf(int $id_reservation, ReservationvolRepository $reservationvolRepository): Response
    {
        $reservation = $reservationvolRepository->find($id_reservation);

        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée.');
        }

        $content = $this->renderView('reservation_vol/pdf_template.html.twig', [
            'reservationvol' => $reservation,
        ]);

        try {
            $html2pdf = new Html2Pdf('P', 'A4', 'fr');
            $html2pdf->writeHTML($content);

            $pdfContent = $html2pdf->output('', 'S');

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="reservation_vol_' . $reservation->getIdReservation() . '.pdf"',
            ]);
        } catch (Html2PdfException $e) {
            return new Response('Erreur lors de la génération du PDF : ' . $e->getMessage(), 500);
        }
    }

    private function generateBarcodes(array $reservations): array
    {
        $generator = new BarcodeGeneratorPNG();
        $barcodes = [];

        foreach ($reservations as $reservation) {
            $barcodeData = base64_encode($generator->getBarcode((string) $reservation->getIdReservation(), $generator::TYPE_CODE_128));
            $barcodes[$reservation->getIdReservation()] = $barcodeData;
        }

        return $barcodes;
    }

    private function generateQRCodes(array $reservations): array
    {
        $qrCodes = [];

        foreach ($reservations as $reservation) {
            $content = sprintf(
                "Vol: %s - Depart: %s - Arrivee: %s - Classe: %s - Prix: %.2f TND",
                $reservation->getVol()->getNumeroVol(),
                $reservation->getVol()->getAeroDepart(),
                $reservation->getVol()->getAeroArrivee(),
                $reservation->getClasseService(),
                $reservation->getVol()->getPrix()
            );
            $qrCodes[$reservation->getIdReservation()] = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($content);
        }

        return $qrCodes;
    }
}