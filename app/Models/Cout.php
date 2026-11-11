<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cout extends Model
{
    protected $table = 'couts';

    protected $fillable = [
        'dossier_id', 'libelle', 'categorie', 'montant', 'date', 'fournisseur',
        'notes', 'saisi_par',
    ];

    protected function casts(): array
    {
        return ['montant' => 'float', 'date' => 'date'];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisi_par');
    }

    public function getCategorieLabelAttribute(): string
    {
        return match ($this->categorie) {
            'transit' => 'Transit',
            'douane' => 'Douane',
            'manutention' => 'Manutention',
            'transport' => 'Transport',
            default => 'Divers',
        };
    }

    public static function categories(): array
    {
        return [
            'transit' => 'Transit',
            'douane' => 'Douane',
            'manutention' => 'Manutention',
            'transport' => 'Transport',
            'divers' => 'Divers',
        ];
    }
}
