<?php

namespace App\Service;

use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Enum\StatutReservation;
use App\Enum\StatutTrajet;
use Doctrine\ORM\EntityManagerInterface;

class TrajetService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function publier(Trajet $trajet, Utilisateur $conducteur): void
    {
        $trajet->setConducteur($conducteur);
        $trajet->setStatut(StatutTrajet::A_VENIR);

        $this->em->persist($trajet);
        $this->em->flush();
    }

    public function peutEtreModifiePar(Trajet $trajet, Utilisateur $utilisateur): bool
    {
        return $trajet->getConducteur() === $utilisateur
            && $trajet->getStatut() === StatutTrajet::A_VENIR
            && !$trajet->estPasse();
    }

    public function enregistrerModification(): void
    {
        $this->em->flush();
    }

    public function annuler(Trajet $trajet): void
    {
        if ($trajet->getStatut() !== StatutTrajet::A_VENIR) {
            throw new \DomainException('Seul un trajet à venir peut être annulé.');
        }

        $trajet->setStatut(StatutTrajet::ANNULE);

        foreach ($trajet->getReservations() as $reservation) {
            if ($reservation->estActive()) {
                $reservation->setStatut(StatutReservation::ANNULEE);
            }
        }

        $this->em->flush();
    }
}
