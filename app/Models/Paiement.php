<?php

namespace App\Models;

use App\Enums\ModePaiement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'facture_id', 'montant', 'mode', 'date_paiement', 'reference',
        'notes', 'enregistre_par',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'float',
            'mode' => ModePaiement::class,
            'date_paiement' => 'date',
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }

    public function getModeLabelAttribute(): string
    {
        return $this->mode->label();
    }
}
