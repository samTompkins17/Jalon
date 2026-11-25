<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agence extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom', 'slug', 'email', 'telephone', 'adresse', 'ville', 'pays',
        'logo_path', 'numero_agrement', 'type', 'devise',
        'dossier_prefixe', 'dossier_annee', 'dossier_increment',
        'facture_prefixe', 'facture_annee', 'facture_increment',
        'client_prefixe', 'client_annee', 'client_increment',
        'tva_taux', 'delai_franchise_defaut', 'active',
        'motif_suspension', 'suspendue_at',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'tva_taux' => 'decimal:2',
            'dossier_annee' => 'integer',
            'facture_annee' => 'integer',
            'client_annee' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class);
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    public function alertes(): HasMany
    {
        return $this->hasMany(Alerte::class);
    }

    public function parametres(): HasMany
    {
        return $this->hasMany(ParametreAgence::class);
    }

    /** Lecture d'un paramètre d'agence avec valeur de repli. */
    public function parametre(string $cle, mixed $defaut = null): mixed
    {
        $parametre = $this->parametres->firstWhere('cle', $cle);

        return $parametre?->valeur ?? $defaut;
    }

    public function getInitialesAttribute(): string
    {
        return Format::initiales($this->nom);
    }
}
