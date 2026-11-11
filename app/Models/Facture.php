<?php

namespace App\Models;

use App\Enums\StatutFacture;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'agence_id', 'client_id', 'dossier_id', 'numero', 'date_facture',
        'date_echeance', 'sous_total', 'remise', 'tva_taux', 'tva_montant',
        'total', 'statut', 'notes', 'emise_par',
    ];

    protected function casts(): array
    {
        return [
            'date_facture' => 'date',
            'date_echeance' => 'date',
            'sous_total' => 'float',
            'remise' => 'float',
            'tva_taux' => 'float',
            'tva_montant' => 'float',
            'total' => 'float',
            'statut' => StatutFacture::class,
        ];
    }

    /** Les URL exposent le numéro de facture plutôt que son identifiant. */
    public function getRouteKeyName(): string
    {
        return 'numero';
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function emetteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emise_par');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(FactureLigne::class)->orderBy('ordre')->orderBy('id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class)->latest('date_paiement');
    }

    /** Montant déjà encaissé. */
    public function getMontantPayeAttribute(): float
    {
        return (float) ($this->paiements_sum_montant ?? $this->paiements()->sum('montant'));
    }

    /** Reste à payer. */
    public function getSoldeAttribute(): float
    {
        return max(0, $this->total - $this->montant_paye);
    }

    public function estEnRetard(): bool
    {
        return $this->statut !== StatutFacture::Payee
            && $this->date_echeance
            && $this->date_echeance->isPast();
    }

    public function getStatutLabelAttribute(): string
    {
        return $this->statut->label();
    }

    public static function libelleStatut(StatutFacture $statut): string
    {
        return $statut->label();
    }
}
