<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Enum\StatutReservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findParPassager(Utilisateur $passager): array
    {
        return $this->createQueryBuilder('r')
            ->addSelect('t', 'c')
            ->join('r.trajet', 't')
            ->join('t.conducteur', 'c')
            ->andWhere('r.passager = :passager')
            ->setParameter('passager', $passager)
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveParPassagerEtTrajet(Utilisateur $passager, Trajet $trajet): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.passager = :passager')
            ->andWhere('r.trajet = :trajet')
            ->andWhere('r.statut IN (:actifs)')
            ->setParameter('passager', $passager)
            ->setParameter('trajet', $trajet)
            ->setParameter('actifs', [StatutReservation::EN_ATTENTE, StatutReservation::CONFIRMEE])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function rafraichirStatuts(array $reservations): void
    {
        $modifie = false;
        foreach ($reservations as $reservation) {
            $trajet = $reservation->getTrajet();
            if ($trajet !== null && $reservation->estActive() && $trajet->estPasse()) {
                $reservation->setStatut(StatutReservation::TERMINEE);
                $modifie = true;
            }
        }

        if ($modifie) {
            $this->getEntityManager()->flush();
        }
    }
}
