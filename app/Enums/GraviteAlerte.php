<?php

namespace App\Enums;

enum GraviteAlerte: string
{
    case Basse = 'basse';
    case Moyenne = 'moyenne';
    case Haute = 'haute';
    case Critique = 'critique';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function badge(): string
    {
        return match ($this) {
            self::Basse => 'neutral',
            self::Moyenne => 'gold',
            self::Haute => 'amber',
            self::Critique => 'pink',
        };
    }

    public function ordre(): int
    {
        return match ($this) {
            self::Critique => 4,
            self::Haute => 3,
            self::Moyenne => 2,
            self::Basse => 1,
        };
    }
}
