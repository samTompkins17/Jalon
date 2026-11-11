<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agence_id', 'transitaire_id', 'code', 'nom', 'type', 'email',
        'telephone', 'whatsapp', 'adresse', 'ville', 'pays',
        'contact_nom', 'contact_telephone', 'contact_email', 'notes', 'statut',
    ];

    /** Les URL exposent le code client plutôt que son identifiant. */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function transitaire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transitaire_id');
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    /** Compte de connexion au portail (créé par le transitaire). */
    public function utilisateur(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function scopeDeLAgence(Builder $query, ?int $agenceId): Builder
    {
        return $query->where('agence_id', $agenceId);
    }

    public function getInitialesAttribute(): string
    {
        return Format::initiales($this->nom);
    }

    /** Montant total facturé (TTC). */
    public function getTotalFactureAttribute(): float
    {
        return (float) ($this->total_facture ?? $this->factures()->sum('total'));
    }

    /** Solde restant dû sur l'ensemble des factures. */
    public function getSoldeDuAttribute(): float
    {
        $total = $this->factures()->sum('total');
        $paye = Paiement::whereIn('facture_id', $this->factures()->select('id'))->sum('montant');

        return (float) ($total - $paye);
    }

    public function getDernierDossierAttribute(): ?Dossier
    {
        return $this->dossiers()->latest('created_at')->first();
    }
}
