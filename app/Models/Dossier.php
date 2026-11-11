<?php

namespace App\Models;

use App\Enums\EcheanceAlerte;
use App\Enums\StatutConteneur;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Dossier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agence_id', 'client_id', 'transitaire_id', 'regime_id', 'reference',
        'code_suivi', 'ligne_maritime', 'navire', 'numero_bl', 'nature_marchandise', 'pays_origine',
        'date_arrivee_prevue', 'jalon_actuel', 'brouillon', 'marge_previsionnelle',
        'date_ouverture', 'date_cloture', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'jalon_actuel' => 'integer',
            'brouillon' => 'boolean',
            'marge_previsionnelle' => 'float',
            'date_arrivee_prevue' => 'date',
            'date_ouverture' => 'date',
            'date_cloture' => 'date',
        ];
    }

    /** Les URL exposent la référence du dossier plutôt que son identifiant. */
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    // ---------------------------------------------------------------- Relations

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transitaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transitaire_id');
    }

    public function regime(): BelongsTo
    {
        return $this->belongsTo(Regime::class);
    }

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueStatut::class)->latest('date_passage')->latest('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function conteneurs(): HasMany
    {
        return $this->hasMany(Conteneur::class);
    }

    public function couts(): HasMany
    {
        return $this->hasMany(Cout::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    // ------------------------------------------------------------------- Jalons

    /** Configuration des 9 jalons (mise en cache pour la requête). */
    public static function jalons(): Collection
    {
        return JalonConfig::tous();
    }

    public function getJalonAttribute(): ?JalonConfig
    {
        return self::jalons()->firstWhere('ordre', $this->jalon_actuel);
    }

    public function getJalonNomAttribute(): string
    {
        return $this->jalon?->nom ?? 'Jalon '.$this->jalon_actuel;
    }

    public function getJalonSuivantAttribute(): ?JalonConfig
    {
        return self::jalons()->firstWhere('ordre', $this->jalon_actuel + 1);
    }

    public function getProgressionAttribute(): string
    {
        return $this->jalon_actuel.' sur 9';
    }

    // ------------------------------------------------------------------ Montants

    /** Total facturé (TTC) sur le dossier. */
    public function getMontantFactureAttribute(): float
    {
        return (float) ($this->factures_sum_total ?? $this->factures()->sum('total'));
    }

    /** Total des coûts réellement saisis. */
    public function getTotalCoutsAttribute(): float
    {
        return (float) ($this->couts_sum_montant ?? $this->couts()->sum('montant'));
    }

    /** Marge réelle = facturé − coûts saisis. */
    public function getMargeReelleAttribute(): float
    {
        return $this->montant_facture - $this->total_couts;
    }

    public function getMargeReelleAfficheeAttribute(): string
    {
        return $this->factures()->exists() || $this->couts()->exists() ? 'réelle' : 'estimée';
    }

    // --------------------------------------------------------------------- État

    /** brouillon | en_cours | en_retard | livre */
    public function getEtatAttribute(): string
    {
        if ($this->brouillon) {
            return 'brouillon';
        }

        if ($this->jalon_actuel >= 9 && $this->date_cloture) {
            return 'livre';
        }

        if ($this->conteneurCritique()) {
            return 'en_retard';
        }

        return 'en_cours';
    }

    public function getEtatLabelAttribute(): string
    {
        return match ($this->etat) {
            'brouillon' => 'Brouillon',
            'livre' => 'Livré',
            'en_retard' => 'En retard',
            default => 'En cours',
        };
    }

    public function getEtatBadgeAttribute(): string
    {
        return match ($this->etat) {
            'brouillon' => 'neutral',
            'livre' => 'success',
            'en_retard' => 'pink',
            default => 'info',
        };
    }

    /** Conteneur au port dont la franchise est dépassée (priorité absolue). */
    public function conteneurCritique(): ?Conteneur
    {
        return $this->conteneurs
            ->where('statut', StatutConteneur::AuPort)
            ->filter(fn (Conteneur $c) => $c->fin_franchise !== null && $c->jours_restants !== null && $c->jours_restants <= 0)
            ->sortBy('fin_franchise')
            ->first();
    }

    /** Conteneur le plus urgent (franchise la plus proche), au port ou non. */
    public function conteneurUrgent(): ?Conteneur
    {
        return $this->conteneurs
            ->where('statut', StatutConteneur::AuPort)
            ->filter(fn (Conteneur $c) => $c->fin_franchise !== null)
            ->sortBy('fin_franchise')
            ->first();
    }

    public function getJoursFranchiseAttribute(): ?int
    {
        return $this->conteneurUrgent()?->jours_restants;
    }

    public function getEcheanceFranchiseAttribute(): ?EcheanceAlerte
    {
        $jours = $this->jours_franchise;

        return $jours === null ? null : EcheanceAlerte::depuisJoursRestants($jours);
    }

    // ------------------------------------------------------------------- Scopes

    /** Un client ne voit que ses dossiers ; les rôles internes voient ceux de leur agence. */
    public function scopeVisiblePour(Builder $query, User $user): Builder
    {
        if ($user->estClient()) {
            return $query->where('client_id', $user->client_id);
        }

        return $query->where('agence_id', $user->agence_id);
    }

    public function estVisiblePar(User $user): bool
    {
        if ($user->estClient()) {
            return $this->client_id === $user->client_id;
        }

        return $this->agence_id === $user->agence_id;
    }

    public function estLivre(): bool
    {
        return $this->jalon_actuel >= 9 && ! $this->brouillon;
    }

    public function getDocumentManquantsAttribute(): array
    {
        $requis = $this->regime?->documentsRequis->pluck('libelle')->all() ?? [];
        $fournis = $this->documents->pluck('type')->unique()->all();

        return array_values(array_diff($requis, $fournis));
    }

    public function getDateFranchiseLaPlusProcheAttribute(): ?CarbonInterface
    {
        return $this->conteneurs->pluck('fin_franchise')->filter()->sort()->first();
    }
}
