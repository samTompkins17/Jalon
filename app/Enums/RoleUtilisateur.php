<?php

namespace App\Enums;

enum RoleUtilisateur: string
{
    case Administrateur = 'administrateur';
    case Transitaire = 'transitaire';
    case Comptable = 'comptable';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::Transitaire => 'Transitaire',
            self::Comptable => 'Comptable',
            self::Client => 'Client',
        };
    }

    /** Rôles internes à l'agence (tout sauf le portail client). */
    public static function internes(): array
    {
        return [self::Administrateur, self::Transitaire, self::Comptable];
    }

    public function estInterne(): bool
    {
        return $this !== self::Client;
    }

    /** Valeurs utilisables dans les formulaires. */
    public static function valeursInternes(): array
    {
        return array_map(fn (self $r) => $r->value, self::internes());
    }
}
