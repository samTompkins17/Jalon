<?php

namespace App\Enums;

enum ModePaiement: string
{
    case Especes = 'especes';
    case Cheque = 'cheque';
    case Virement = 'virement';
    case MobileMoney = 'mobile_money';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Especes => 'Espèces',
            self::Cheque => 'Chèque',
            self::Virement => 'Virement',
            self::MobileMoney => 'Mobile Money',
            self::Autre => 'Autre',
        };
    }
}
