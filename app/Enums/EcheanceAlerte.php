<?php

namespace App\Enums;

enum EcheanceAlerte: string
{
    case J2 = 'J-2';
    case J1 = 'J-1';
    case J0 = 'J0';

    /** Nombre de jours avant la fin de franchise. */
    public function joursAvant(): int
    {
        return match ($this) {
            self::J2 => 2,
            self::J1 => 1,
            self::J0 => 0,
        };
    }

    public function gravite(): GraviteAlerte
    {
        return match ($this) {
            self::J2 => GraviteAlerte::Moyenne,
            self::J1 => GraviteAlerte::Haute,
            self::J0 => GraviteAlerte::Critique,
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::J2 => 'gold-soft',
            self::J1 => 'amber',
            self::J0 => 'pink',
        };
    }

    public static function depuisJoursRestants(int $jours): ?self
    {
        return match ($jours) {
            2 => self::J2,
            1 => self::J1,
            0 => self::J0,
            default => null,
        };
    }
}
