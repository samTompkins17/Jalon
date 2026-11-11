<?php

namespace App\Enums;

enum StatutActivation: string
{
    case EnAttente = 'en_attente';
    case Actif = 'actif';
    case Expire = 'expire';

    public function label(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente d\'activation',
            self::Actif => 'Actif',
            self::Expire => 'Invitation expirée',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::EnAttente => 'warning',
            self::Actif => 'success',
            self::Expire => 'danger',
        };
    }
}
