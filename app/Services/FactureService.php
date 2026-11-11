<?php

namespace App\Services;

use App\Enums\StatutFacture;
use App\Models\Facture;

/**
 * Facturation : totaux TTC et statut (impayée / partielle / payée).
 * Montants en FCFA, arrondis à l'unité.
 */
class FactureService
{
    public function recalculer(Facture $facture): Facture
    {
        $facture->loadMissing(['lignes', 'paiements']);

        $sousTotal = (float) $facture->lignes->sum('montant');
        $remise = min((float) $facture->remise, $sousTotal);
        $base = max(0, $sousTotal - $remise);
        $tva = round($base * ((float) $facture->tva_taux / 100));
        $total = $base + $tva;

        $paye = (float) $facture->paiements->sum('montant');

        $statut = match (true) {
            $paye <= 0 => StatutFacture::Impayee,
            $paye >= $total => StatutFacture::Payee,
            default => StatutFacture::Partielle,
        };

        $facture->forceFill([
            'sous_total' => $sousTotal,
            'remise' => $remise,
            'tva_montant' => $tva,
            'total' => $total,
            'statut' => $statut,
        ])->save();

        // Le total TTC du dossier est mis en cache sur les instances déjà chargées.
        $facture->unsetRelation('paiements');

        return $facture;
    }

    /** Recalcule après ajout / suppression d'un paiement. */
    public function rafraichirStatut(Facture $facture): void
    {
        $this->recalculer($facture);
    }
}
