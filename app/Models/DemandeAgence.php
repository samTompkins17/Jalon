<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandeAgence extends Model
{
    protected $table = 'demandes_agence';

    protected $fillable = [
        'nom_agence', 'nom_contact', 'email', 'telephone', 'numero_agrement',
        'statut', 'motif_refus', 'traitee_par', 'agence_id', 'traitee_at',
        'notes', 'dernier_contact_at',
    ];

    protected function casts(): array
    {
        return [
            'traitee_at' => 'datetime',
            'dernier_contact_at' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function administrateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traitee_par');
    }
}
