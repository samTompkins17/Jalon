<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'dossier_id', 'type', 'nom_original', 'chemin', 'mime', 'taille',
        'valide', 'valide_par', 'date_validation', 'visible_client', 'ajoute_par',
    ];

    protected function casts(): array
    {
        return [
            'valide' => 'boolean',
            'visible_client' => 'boolean',
            'date_validation' => 'datetime',
            'taille' => 'integer',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ajoute_par');
    }

    public function existeSurDisque(): bool
    {
        return $this->chemin && Storage::disk('local')->exists($this->chemin);
    }

    public function getTailleLisibleAttribute(): string
    {
        $octets = $this->taille;
        $unites = ['o', 'Ko', 'Mo', 'Go'];

        $index = 0;
        while ($octets >= 1024 && $index < count($unites) - 1) {
            $octets /= 1024;
            $index++;
        }

        return number_format($octets, $index === 0 ? 0 : 1, ',', ' ').' '.$unites[$index];
    }

    public function getExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->nom_original, PATHINFO_EXTENSION));
    }
}
