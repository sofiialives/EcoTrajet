<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RechercheTrajetCriteres
{
    public const TRI_PRIX = 'prix';
    public const TRI_HEURE = 'heure';
    public const TRI_DISTANCE = 'distance';

    #[Assert\Length(max: 120)]
    public ?string $villeDepart = null;

    #[Assert\Length(max: 120)]
    public ?string $villeArrivee = null;

    public ?\DateTimeImmutable $date = null;

    public ?\DateTimeImmutable $heure = null;

    #[Assert\PositiveOrZero(message: 'Le prix maximum doit être positif.')]
    public ?float $prixMax = null;

    #[Assert\Range(min: 1, max: 8, notInRangeMessage: 'Le nombre de places doit être compris entre {{ min }} et {{ max }}.')]
    public ?int $nombrePlaces = null;

    #[Assert\Positive(message: 'La distance maximale doit être positive.')]
    public ?int $distanceMax = null;

    #[Assert\Choice(choices: [self::TRI_PRIX, self::TRI_HEURE, self::TRI_DISTANCE])]
    public string $tri = self::TRI_HEURE;

    public function estVide(): bool
    {
        return $this->villeDepart === null
            && $this->villeArrivee === null
            && $this->date === null
            && $this->heure === null
            && $this->prixMax === null
            && $this->nombrePlaces === null
            && $this->distanceMax === null;
    }
}
