<?php

namespace App\Console\Commands;

use App\Services\AlerteService;
use Illuminate\Console\Command;

class GenererAlertes extends Command
{
    protected $signature = 'jalon:alertes';

    protected $description = 'Génère les alertes de surestarie J-2, J-1 et J0 des conteneurs au port.';

    public function handle(AlerteService $service): int
    {
        $nombre = $service->generer();

        $this->info($nombre.' alerte'.($nombre > 1 ? 's' : '').' créée'.($nombre > 1 ? 's' : '').'.');

        return self::SUCCESS;
    }
}
