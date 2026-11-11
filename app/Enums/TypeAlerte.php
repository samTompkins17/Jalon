<?php

namespace App\Enums;

enum TypeAlerte: string
{
    case Franchise = 'franchise';
    case Document = 'document';
    case Paiement = 'paiement';
    case Arrivee = 'arrivee';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Franchise => 'Surestarie',
            self::Document => 'Document',
            self::Paiement => 'Paiement',
            self::Arrivee => 'Arrivée',
            self::Autre => 'Autre',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::Franchise => 'clock',
            self::Document => 'document',
            self::Paiement => 'receipt',
            self::Arrivee => 'ship',
            self::Autre => 'bell',
        };
    }
}
