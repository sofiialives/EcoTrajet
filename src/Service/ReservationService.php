<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Enum\StatutReservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReservationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ReservationRepository $reservationRepository,
    ) {
    }

    public function verifierReservation(Trajet $trajet, Utilisateur $passager, int $nombrePlaces): ?string
    {
        if ($trajet->getConducteur() === $passager) {
            return 'Vous ne pouvez pas réserver votre propre trajet.';
        }

        if ($trajet->estPasse()) {
            return 'Ce trajet est déjà passé.';
        }

        if (!$trajet->estReservable()) {
            return 'Ce trajet est complet ou n’est plus disponible.';
        }

        if ($this->reservationRepository->findActiveParPassagerEtTrajet($passager, $trajet) !== null) {
            return 'Vous avez déjà une réservation en cours pour ce trajet.';
        }

        if ($nombrePlaces < 1) {
            return 'Veuillez réserver au moins une place.';
        }

        if ($nombrePlaces > $trajet->getPlacesRestantes()) {
            return \sprintf(
                'Il ne reste que %d place(s) disponible(s) pour ce trajet.',
                $trajet->getPlacesRestantes(),
            );
        }

        return null;
    }

    public function calculerPrixTotal(Trajet $trajet, int $nombrePlaces): string
    {
        $prix = (float) $trajet->getPrixParPlace() * $nombrePlaces;

        return number_format($prix, 2, '.', '');
    }

    public function reserver(Trajet $trajet, Utilisateur $passager, int $nombrePlaces): Reservation
    {
        $erreur = $this->verifierReservation($trajet, $passager, $nombrePlaces);
        if ($erreur !== null) {
            throw new \DomainException($erreur);
        }

        $reservation = new Reservation();
        $reservation->setPassager($passager);
        $reservation->setTrajet($trajet);
        $reservation->setNombrePlaces($nombrePlaces);
        $reservation->setPrixTotal($this->calculerPrixTotal($trajet, $nombrePlaces));
        $reservation->setStatut(StatutReservation::CONFIRMEE);

        $this->em->persist($reservation);
        $this->em->flush();

        return $reservation;
    }

    public function annuler(Reservation $reservation): void
    {
        if (!$reservation->estAnnulable()) {
            throw new \DomainException('Cette réservation ne peut plus être annulée.');
        }

        $reservation->setStatut(StatutReservation::ANNULEE);
        $this->em->flush();
    }
}
