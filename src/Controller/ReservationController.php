<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Form\ReservationType;
use App\Service\ReservationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    public function __construct(
        private readonly ReservationService $reservationService,
    ) {
    }

    #[Route('/trajets/{id<\d+>}/reserver', name: 'app_reservation_reserver')]
    #[IsGranted('ROLE_USER')]
    public function reserver(Trajet $trajet, Request $request): Response
    {
        /** @var Utilisateur $passager */
        $passager = $this->getUser();

        $erreur = $this->reservationService->verifierReservation($trajet, $passager, 1);
        if ($erreur !== null) {
            $this->addFlash('erreur', $erreur);

            return $this->redirectToRoute('app_trajet_details', ['id' => $trajet->getId()]);
        }

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation, [
            'places_max' => $trajet->getPlacesRestantes(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $reservation = $this->reservationService->reserver(
                    $trajet,
                    $passager,
                    (int) $reservation->getNombrePlaces(),
                );

                return $this->redirectToRoute('app_reservation_confirmee', ['id' => $reservation->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('erreur', $e->getMessage());
            }
        }

        return $this->render('reservation/reserver.html.twig', [
            'trajet' => $trajet,
            'form' => $form,
        ]);
    }

    #[Route('/reservations/{id<\d+>}/confirmee', name: 'app_reservation_confirmee')]
    #[IsGranted('ROLE_USER')]
    public function confirmee(Reservation $reservation): Response
    {
        if ($reservation->getPassager() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/confirmee.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservations/{id<\d+>}/annuler', name: 'app_reservation_annuler', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function annuler(Reservation $reservation, Request $request): Response
    {
        if ($reservation->getPassager() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('annuler_reservation_'.$reservation->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('erreur', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToRoute('app_mes_trajets', ['onglet' => 'reservations']);
        }

        try {
            $this->reservationService->annuler($reservation);
            $this->addFlash('succes', 'Votre réservation a bien été annulée.');
        } catch (\DomainException $e) {
            $this->addFlash('erreur', $e->getMessage());
        }

        return $this->redirectToRoute('app_mes_trajets', ['onglet' => 'reservations']);
    }
}
