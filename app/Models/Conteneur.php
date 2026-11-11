<?php

namespace App\Models;

use App\Enums\EcheanceAlerte;
use App\Enums\StatutConteneur;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Conteneur extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id', 'numero', 'type', 'taille', 'statut', 'date_arrivee',
        'date_sortie', 'date_restitution', 'fin_franchise', 'ligne_maritime',
        'numero_plomb', 'poids_kg', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'statut' => StatutConteneur::class,
            'date_arrivee' => 'date',
            'date_sortie' => 'date',
            'date_restitution' => 'date',
            'fin_franchise' => 'date',
            'poids_kg' => 'decimal:2',
        ];
    }

    /** Les URL exposent le numéro du conteneur plutôt que son identifiant. */
    public function getRouteKeyName(): string
    {
        return 'numero';
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    /** Historique des événements du conteneur (détail). */
    public function evenements(): HasMany
    {
        return $this->hasMany(EvenementConteneur::class)->orderByDesc('date_evenement')->orderByDesc('id');
    }

    /** Dernier événement enregistré pour l'historique du conteneur. */
    public function dernierEvenement(): HasOne
    {
        return $this->hasOne(EvenementConteneur::class)->latestOfMany();
    }

    /**
     * Jours restants avant la fin de franchise (négatif si dépassée).
     */
    public function getJoursRestantsAttribute(): ?int
    {
        if (! $this->fin_franchise) {
            return null;
        }

        // Positif si la fin de franchise est dans le futur, négatif si dépassée.
        return (int) Carbon::today()->diffInDays($this->fin_franchise, false);
    }

    /** « Dépassée », « 1 jour », « 2 jours » ou « — ». */
    public function getEtatFranchiseAttribute(): string
    {
        if ($this->statut !== StatutConteneur::AuPort) {
            return '—';
        }

        $jours = $this->jours_restants;

        if ($jours === null) {
            return '—';
        }

        return $jours <= 0 ? 'Dépassée' : $jours.' '.($jours > 1 ? 'jours' : 'jour');
    }

    public function getEcheanceAttribute(): ?EcheanceAlerte
    {
        if ($this->statut !== StatutConteneur::AuPort || $this->jours_restants === null) {
            return null;
        }

        return EcheanceAlerte::depuisJoursRestants($this->jours_restants);
    }

    public function estEnSurestarie(): bool
    {
        return $this->statut === StatutConteneur::AuPort
            && $this->jours_restants !== null
            && $this->jours_restants <= 0;
    }

    public function getNumeroFormateAttribute(): string
    {
        // MSCU4521870 => « MSCU 4521870 »
        return strlen($this->numero) > 4
            ? substr($this->numero, 0, 4).' '.substr($this->numero, 4)
            : $this->numero;
    }

    public function getDateFranchiseAttribute(): ?CarbonInterface
    {
        return $this->fin_franchise;
    }
}
