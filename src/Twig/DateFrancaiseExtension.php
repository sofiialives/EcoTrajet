<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class DateFrancaiseExtension extends AbstractExtension
{
    private const JOURS_COURTS = ['lun.', 'mar.', 'mer.', 'jeu.', 'ven.', 'sam.', 'dim.'];
    private const JOURS_LONGS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
    private const MOIS_COURTS = ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'];
    private const MOIS_LONGS = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    public function getFilters(): array
    {
        return [
            new TwigFilter('date_fr', $this->dateFr(...)),
            new TwigFilter('date_fr_longue', $this->dateFrLongue(...)),
        ];
    }

    public function dateFr(\DateTimeInterface $date, bool $avecAnnee = false): string
    {
        $texte = \sprintf(
            '%s %d %s',
            self::JOURS_COURTS[(int) $date->format('N') - 1],
            (int) $date->format('j'),
            self::MOIS_COURTS[(int) $date->format('n') - 1],
        );

        return $avecAnnee ? $texte.' '.$date->format('Y') : $texte;
    }

    public function dateFrLongue(\DateTimeInterface $date): string
    {
        return \sprintf(
            '%s %d %s %s',
            self::JOURS_LONGS[(int) $date->format('N') - 1],
            (int) $date->format('j'),
            self::MOIS_LONGS[(int) $date->format('n') - 1],
            $date->format('Y'),
        );
    }
}
