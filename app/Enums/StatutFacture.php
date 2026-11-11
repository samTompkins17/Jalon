<?php

namespace App\Enums;

enum StatutFacture: string
{
    case Impayee = 'impayee';
    case Partielle = 'partielle';
    case Payee = 'payee';

    public function label(): string
    {
        return match ($this) {
            self::Impayee => 'Impayée',
            self::Partielle => 'Partiellement payée',
            self::Payee => 'Payée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::Impayee => 'pink',
            self::Partielle => 'gold',
            self::Payee => 'success',
        };
    }
}
