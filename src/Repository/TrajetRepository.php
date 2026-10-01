<?php

namespace App\Repository;

use App\Dto\RechercheTrajetCriteres;
use App\Entity\Trajet;
use App\Entity\Utilisateur;
use App\Enum\StatutReservation;
use App\Enum\StatutTrajet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Trajet>
 */
class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    public function rechercher(RechercheTrajetCriteres $criteres): array
    {
        $qb = $this->createQueryBuilder('t')
            ->addSelect('c')
            ->join('t.conducteur', 'c')
            ->leftJoin('t.reservations', 'r', 'WITH', 'r.statut IN (:statutsActifs)')
            ->andWhere('t.statut = :aVenir')
            ->setParameter('statutsActifs', [StatutReservation::EN_ATTENTE, StatutReservation::CONFIRMEE])
            ->setParameter('aVenir', StatutTrajet::A_VENIR)
            ->groupBy('t.id')
            ->addGroupBy('c.id');

        if ($criteres->villeDepart) {
            $qb->andWhere('LOWER(t.villeDepart) LIKE :villeDepart')
                ->setParameter('villeDepart', '%'.mb_strtolower($criteres->villeDepart).'%');
        }

        if ($criteres->villeArrivee) {
            $qb->andWhere('LOWER(t.villeArrivee) LIKE :villeArrivee')
                ->setParameter('villeArrivee', '%'.mb_strtolower($criteres->villeArrivee).'%');
        }

        if ($criteres->date !== null) {
            $qb->andWhere('t.date = :date')
                ->setParameter('date', $criteres->date->setTime(0, 0));
        } else {
            $qb->andWhere('t.date >= :aujourdhui')
                ->setParameter('aujourdhui', (new \DateTimeImmutable('today')));
        }

        if ($criteres->heure !== null) {
            $qb->andWhere('t.heure >= :heure')
                ->setParameter('heure', $criteres->heure);
        }

        if ($criteres->prixMax !== null) {
            $qb->andWhere('t.prixParPlace <= :prixMax')
                ->setParameter('prixMax', number_format($criteres->prixMax, 2, '.', ''));
        }

        if ($criteres->distanceMax !== null) {
            $qb->andWhere('t.distanceKm <= :distanceMax')
                ->setParameter('distanceMax', $criteres->distanceMax);
        }

        $placesVoulues = max(1, (int) ($criteres->nombrePlaces ?? 1));
        $qb->having('(t.nombrePlaces - COALESCE(SUM(r.nombrePlaces), 0)) >= :placesVoulues')
            ->setParameter('placesVoulues', $placesVoulues);

        match ($criteres->tri) {
            RechercheTrajetCriteres::TRI_PRIX => $qb->orderBy('t.prixParPlace', 'ASC'),
            RechercheTrajetCriteres::TRI_DISTANCE => $qb->orderBy('t.distanceKm', 'ASC'),
            default => $qb->orderBy('t.date', 'ASC')->addOrderBy('t.heure', 'ASC'),
        };

        return $qb->getQuery()->getResult();
    }

    public function findParConducteur(Utilisateur $conducteur): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.conducteur = :conducteur')
            ->setParameter('conducteur', $conducteur)
            ->orderBy('t.date', 'DESC')
            ->addOrderBy('t.heure', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function rafraichirStatuts(array $trajets): void
    {
        $maintenant = new \DateTimeImmutable();
        $modifie = false;

        foreach ($trajets as $trajet) {
            if ($trajet->getStatut() === StatutTrajet::ANNULE) {
                continue;
            }
            $depart = $trajet->getDepartLe();
            if ($depart === null) {
                continue;
            }

            $nouveau = $trajet->getStatut();
            if ($depart <= $maintenant && $depart > $maintenant->modify('-3 hours')) {
                $nouveau = StatutTrajet::EN_COURS;
            } elseif ($depart <= $maintenant->modify('-3 hours')) {
                $nouveau = StatutTrajet::TERMINE;
            }

            if ($nouveau !== $trajet->getStatut()) {
                $trajet->setStatut($nouveau);
                $modifie = true;
            }
        }

        if ($modifie) {
            $this->getEntityManager()->flush();
        }
    }
}
