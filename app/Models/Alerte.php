<?php

namespace App\Models;

use App\Enums\GraviteAlerte;
use App\Enums\TypeAlerte;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alerte extends Model
{
    protected $fillable = [
        'agence_id', 'dossier_id', 'conteneur_id', 'type', 'titre', 'description',
        'cible', 'echeance', 'gravite', 'lue', 'lue_at', 'date_alerte',
        'statut_envoi', 'envoyee_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeAlerte::class,
            'gravite' => GraviteAlerte::class,
            'lue' => 'boolean',
            'lue_at' => 'datetime',
            'date_alerte' => 'date',
            'envoyee_at' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function conteneur(): BelongsTo
    {
        return $this->belongsTo(Conteneur::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function scopeNonLues(Builder $query): Builder
    {
        return $query->where('lue', false);
    }

    public function scopeCritiques(Builder $query): Builder
    {
        return $query->where('gravite', GraviteAlerte::Critique);
    }

    public function scopeDeLAgence(Builder $query, ?int $agenceId): Builder
    {
        return $query->where('agence_id', $agenceId);
    }

    public function marquerLue(): void
    {
        if (! $this->lue) {
            $this->forceFill(['lue' => true, 'lue_at' => now()])->save();
        }
    }

    public function marquerNonLue(): void
    {
        if ($this->lue) {
            $this->forceFill(['lue' => false, 'lue_at' => null])->save();
        }
    }

    public function getLienAttribute(): string
    {
        if ($this->dossier_id) {
            return route('dossiers.show', $this->dossier).'?onglet=alertes';
        }

        return route('alertes.index');
    }
}
