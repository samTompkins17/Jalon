<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\JournalActivite;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class JournalService
{
    /**
     * Trace une action dans le journal de bord de l'agence.
     */
    public function enregistrer(
        string $action,
        ?string $description = null,
        ?Dossier $dossier = null,
        ?User $user = null,
        ?Model $sujet = null,
    ): JournalActivite {
        $user ??= auth()->user();

        return JournalActivite::create([
            'agence_id' => $dossier?->agence_id ?? $user?->agence_id,
            'user_id' => $user?->id,
            'dossier_id' => $dossier?->id,
            'action' => $action,
            'description' => $description,
            'sujet_type' => $sujet ? class_basename($sujet) : ($dossier ? 'Dossier' : null),
            'sujet_id' => $sujet?->getKey() ?? $dossier?->id,
        ]);
    }
}
