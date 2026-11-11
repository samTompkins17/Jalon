<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regime extends Model
{
    protected $fillable = ['code', 'nom', 'description', 'actif', 'ordre'];

    protected function casts(): array
    {
        return ['actif' => 'boolean', 'ordre' => 'integer'];
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function franchises(): HasMany
    {
        return $this->hasMany(RegimeFranchise::class);
    }

    public function documentsRequis(): HasMany
    {
        return $this->hasMany(DocumentRequisRegime::class)->orderBy('ordre')->orderBy('id');
    }

    public function getLibelleAttribute(): string
    {
        return $this->nom.' ('.$this->code.')';
    }

    /**
     * Délai de franchise pour une ligne maritime donnée.
     * Repli : ligne générique « * », puis délai par défaut de l'agence.
     */
    public function franchisePour(string $ligne, int $defaut = 4): int
    {
        $franchise = $this->franchises
            ->firstWhere('ligne_maritime', $ligne)
            ?? $this->franchises->firstWhere('ligne_maritime', '*');

        return (int) ($franchise->jours ?? $defaut);
    }
}
