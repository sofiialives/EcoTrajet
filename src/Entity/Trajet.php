<?php

namespace App\Entity;

use App\Enum\StatutReservation;
use App\Enum\StatutTrajet;
use App\Repository\TrajetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrajetRepository::class)]
#[ORM\Table(name: 'trajet')]
class Trajet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Veuillez indiquer la ville de départ.')]
    #[Assert\Length(max: 120)]
    private ?string $villeDepart = null;

    #[ORM\Column(length: 120)]
    #[Assert\NotBlank(message: 'Veuillez indiquer la ville d’arrivée.')]
    #[Assert\Length(max: 120)]
    #[Assert\Expression(
        'value != this.getVilleDepart()',
        message: 'La ville d’arrivée doit être différente de la ville de départ.',
    )]
    private ?string $villeArrivee = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: 'Veuillez choisir une date.')]
    #[Assert\GreaterThanOrEqual('today', message: 'La date du trajet ne peut pas être dans le passé.')]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Veuillez choisir une heure de départ.')]
    private ?\DateTimeImmutable $heure = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Veuillez indiquer la distance.')]
    #[Assert\Positive(message: 'La distance doit être un nombre positif.')]
    #[Assert\LessThanOrEqual(2000, message: 'La distance ne peut pas dépasser {{ compared_value }} km.')]
    private ?int $distanceKm = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Veuillez indiquer le nombre de places.')]
    #[Assert\Range(notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}.', min: 1, max: 8)]
    private ?int $nombrePlaces = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    #[Assert\NotNull(message: 'Veuillez indiquer le prix par place.')]
    #[Assert\PositiveOrZero(message: 'Le prix ne peut pas être négatif.')]
    #[Assert\LessThanOrEqual(500, message: 'Le prix par place ne peut pas dépasser {{ compared_value }} €.')]
    private ?string $prixParPlace = null;

    #[ORM\Column(length: 120, nullable: true)]
    #[Assert\Length(max: 120)]
    private ?string $vehicule = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez indiquer un point de rendez-vous.')]
    #[Assert\Length(max: 255)]
    private ?string $pointRendezVous = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 2000, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    #[ORM\Column(enumType: StatutTrajet::class)]
    private StatutTrajet $statut = StatutTrajet::A_VENIR;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dateCreation;

    #[ORM\ManyToOne(inversedBy: 'trajets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $conducteur = null;

    /** @var Collection<int, Reservation> */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'trajet', orphanRemoval: true)]
    private Collection $reservations;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVilleDepart(): ?string
    {
        return $this->villeDepart;
    }

    public function setVilleDepart(string $villeDepart): static
    {
        $this->villeDepart = $villeDepart;

        return $this;
    }

    public function getVilleArrivee(): ?string
    {
        return $this->villeArrivee;
    }

    public function setVilleArrivee(string $villeArrivee): static
    {
        $this->villeArrivee = $villeArrivee;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHeure(): ?\DateTimeImmutable
    {
        return $this->heure;
    }

    public function setHeure(?\DateTimeImmutable $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getDistanceKm(): ?int
    {
        return $this->distanceKm;
    }

    public function setDistanceKm(?int $distanceKm): static
    {
        $this->distanceKm = $distanceKm;

        return $this;
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

    public function getPrixParPlace(): ?string
    {
        return $this->prixParPlace;
    }

    public function setPrixParPlace(?string $prixParPlace): static
    {
        $this->prixParPlace = $prixParPlace;

        return $this;
    }

    public function getVehicule(): ?string
    {
        return $this->vehicule;
    }

    public function setVehicule(?string $vehicule): static
    {
        $this->vehicule = $vehicule;

        return $this;
    }

    public function getPointRendezVous(): ?string
    {
        return $this->pointRendezVous;
    }

    public function setPointRendezVous(string $pointRendezVous): static
    {
        $this->pointRendezVous = $pointRendezVous;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): StatutTrajet
    {
        return $this->statut;
    }

    public function setStatut(StatutTrajet $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getDateCreation(): \DateTimeImmutable
    {
        return $this->dateCreation;
    }

    public function getConducteur(): ?Utilisateur
    {
        return $this->conducteur;
    }

    public function setConducteur(?Utilisateur $conducteur): static
    {
        $this->conducteur = $conducteur;

        return $this;
    }

    /** @return Collection<int, Reservation> */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setTrajet($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        $this->reservations->removeElement($reservation);

        return $this;
    }

    public function getPlacesReservees(): int
    {
        $total = 0;
        foreach ($this->reservations as $reservation) {
            if (\in_array($reservation->getStatut(), [StatutReservation::EN_ATTENTE, StatutReservation::CONFIRMEE], true)) {
                $total += $reservation->getNombrePlaces();
            }
        }

        return $total;
    }

    public function getPlacesRestantes(): int
    {
        return max(0, (int) $this->nombrePlaces - $this->getPlacesReservees());
    }

    public function estComplet(): bool
    {
        return $this->getPlacesRestantes() === 0;
    }

    /** Date + heure combinées pour les comparaisons. */
    public function getDepartLe(): ?\DateTimeImmutable
    {
        if ($this->date === null || $this->heure === null) {
            return null;
        }

        return $this->date->setTime((int) $this->heure->format('H'), (int) $this->heure->format('i'));
    }

    public function estPasse(): bool
    {
        $depart = $this->getDepartLe();

        return $depart !== null && $depart < new \DateTimeImmutable();
    }

    public function estReservable(): bool
    {
        return $this->statut === StatutTrajet::A_VENIR && !$this->estComplet() && !$this->estPasse();
    }

    public function getItineraire(): string
    {
        return $this->villeDepart.' → '.$this->villeArrivee;
    }
}
