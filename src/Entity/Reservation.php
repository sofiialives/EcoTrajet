<?php

namespace App\Entity;

use App\Enum\StatutReservation;
use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
#[ORM\UniqueConstraint(name: 'uniq_passager_trajet', columns: ['passager_id', 'trajet_id'])]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $passager = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Trajet $trajet = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateReservation;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Veuillez indiquer le nombre de places.')]
    #[Assert\Range(notInRangeMessage: 'Vous pouvez réserver entre {{ min }} et {{ max }} places.', min: 1, max: 8)]
    private ?int $nombrePlaces = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 7, scale: 2)]
    #[Assert\PositiveOrZero]
    private ?string $prixTotal = null;

    #[ORM\Column(enumType: StatutReservation::class)]
    private StatutReservation $statut = StatutReservation::EN_ATTENTE;

    public function __construct()
    {
        $this->dateReservation = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassager(): ?Utilisateur
    {
        return $this->passager;
    }

    public function setPassager(?Utilisateur $passager): static
    {
        $this->passager = $passager;

        return $this;
    }

    public function getTrajet(): ?Trajet
    {
        return $this->trajet;
    }

    public function setTrajet(?Trajet $trajet): static
    {
        $this->trajet = $trajet;

        return $this;
    }

    public function getDateReservation(): \DateTimeImmutable
    {
        return $this->dateReservation;
    }

    public function getNombrePlaces(): ?int
    {
        return $this->nombrePlaces;
    }

    public function setNombrePlaces(?int $nombrePlaces): static
    {
        $this->nombrePlaces = $nombrePlaces;

        return $this;
    }

    public function getPrixTotal(): ?string
    {
        return $this->prixTotal;
    }

    public function setPrixTotal(?string $prixTotal): static
    {
        $this->prixTotal = $prixTotal;

        return $this;
    }

    public function getStatut(): StatutReservation
    {
        return $this->statut;
    }

    public function setStatut(StatutReservation $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function estActive(): bool
    {
        return \in_array($this->statut, [StatutReservation::EN_ATTENTE, StatutReservation::CONFIRMEE], true);
    }

    public function estAnnulable(): bool
    {
        return $this->estActive() && $this->trajet !== null && !$this->trajet->estPasse();
    }
}
