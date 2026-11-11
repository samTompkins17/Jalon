<?php

namespace App\Enums;

enum StatutConteneur: string
{
    case AuPort = 'au_port';
    case Sorti = 'sorti';
    case Restitue = 'restitue';

    public function label(): string
    {
        return match ($this) {
            self::AuPort => 'Au port',
            self::Sorti => 'Sorti',
            self::Restitue => 'Restitué',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::AuPort => 'info',
            self::Sorti => 'neutral',
            self::Restitue => 'success',
        };
    }
}
