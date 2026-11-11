<?php

namespace App\Services;

use App\Models\Conteneur;
use App\Models\Dossier;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Calcul des fins de franchise (surestarie) : jours calendaires,
 * paramétrables par régime ET par ligne maritime.
 */
class FranchiseService
{
    public function joursDeFranchise(Dossier $dossier, ?string $ligne = null): int
    {
        $ligne ??= $dossier->ligne_maritime ?? '*';
        $defaut = (int) ($dossier->agence?->delai_franchise_defaut ?? 4);

        $dossier->loadMissing('regime.franchises');

        return $dossier->regime?->franchisePour($ligne, $defaut) ?? $defaut;
    }

    public function finFranchise(Dossier $dossier, CarbonInterface $arrivee, ?string $ligne = null): Carbon
    {
        return Carbon::parse($arrivee)->startOfDay()->addDays($this->joursDeFranchise($dossier, $ligne));
    }

    /** Recalcule et enregistre la fin de franchise d'un conteneur. */
    public function appliquer(Conteneur $conteneur, ?string $ligne = null): Conteneur
    {
        if ($conteneur->date_arrivee) {
            $conteneur->fin_franchise = $this->finFranchise(
                $conteneur->dossier,
                $conteneur->date_arrivee,
                $ligne ?? $conteneur->ligne_maritime
            );
            $conteneur->save();
        }

        return $conteneur;
    }

    /** Nombre de jours restants avant la fin de franchise (négatif si dépassée). */
    public function joursRestants(?CarbonInterface $fin, ?CarbonInterface $reference = null): ?int
    {
        if (! $fin) {
            return null;
        }

        $reference ??= Carbon::today();

        // Positif si $fin est dans le futur, négatif si la date est dépassée.
        return (int) Carbon::parse($reference)->startOfDay()->diffInDays(Carbon::parse($fin)->startOfDay(), false);
    }
}
