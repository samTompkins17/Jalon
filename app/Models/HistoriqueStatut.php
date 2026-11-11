<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueStatut extends Model
{
    protected $table = 'historique_statuts';

    protected $fillable = [
        'dossier_id', 'user_id', 'jalon', 'jalon_precedent', 'action',
        'commentaire', 'date_passage',
    ];

    protected function casts(): array
    {
        return [
            'date_passage' => 'date',
            'jalon' => 'integer',
            'jalon_precedent' => 'integer',
        ];
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'creation' => 'Dossier créé',
            'recul' => 'Recul de jalon',
            'modification' => 'Modification',
            default => 'Jalon franchi',
        };
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->action) {
            'recul' => 'danger',
            'modification' => 'neutral',
            'creation' => 'info',
            default => 'success',
        };
    }
}
