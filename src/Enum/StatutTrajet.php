<?php

namespace App\Enum;

enum StatutTrajet: string
{
    case A_VENIR = 'a_venir';
    case EN_COURS = 'en_cours';
    case TERMINE = 'termine';
    case ANNULE = 'annule';

    public function libelle(): string
    {
        return match ($this) {
            self::A_VENIR => 'À venir',
            self::EN_COURS => 'En cours',
            self::TERMINE => 'Terminé',
            self::ANNULE => 'Annulé',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::A_VENIR => 'badge--avenir',
            self::EN_COURS => 'badge--encours',
            self::TERMINE => 'badge--termine',
            self::ANNULE => 'badge--annule',
        };
    }
}
