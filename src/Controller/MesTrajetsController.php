<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\ReservationRepository;
use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MesTrajetsController extends AbstractController
{
    #[Route('/mes-trajets', name: 'app_mes_trajets')]
    #[IsGranted('ROLE_USER')]
    public function index(
        Request $request,
        TrajetRepository $trajetRepository,
        ReservationRepository $reservationRepository,
    ): Response {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        $trajets = $trajetRepository->findParConducteur($utilisateur);
        $reservations = $reservationRepository->findParPassager($utilisateur);

        $trajetRepository->rafraichirStatuts($trajets);
        $reservationRepository->rafraichirStatuts($reservations);

        $onglet = $request->query->get('onglet', 'crees');
        if (!\in_array($onglet, ['crees', 'reservations'], true)) {
            $onglet = 'crees';
        }

        return $this->render('mes_trajets/index.html.twig', [
            'trajets' => $trajets,
            'reservations' => $reservations,
            'onglet' => $onglet,
        ]);
    }
}
