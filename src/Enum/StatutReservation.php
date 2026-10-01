<?php

namespace App\Enum;

enum StatutReservation: string
{
    case EN_ATTENTE = 'en_attente';
    case CONFIRMEE = 'confirmee';
    case ANNULEE = 'annulee';
    case TERMINEE = 'terminee';

    public function libelle(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::CONFIRMEE => 'Confirmée',
            self::ANNULEE => 'Annulée',
            self::TERMINEE => 'Terminée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'badge--attente',
            self::CONFIRMEE => 'badge--confirmee',
            self::ANNULEE => 'badge--annule',
            self::TERMINEE => 'badge--termine',
        };
    }
}
