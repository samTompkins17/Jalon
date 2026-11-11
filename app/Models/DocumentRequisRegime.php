<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequisRegime extends Model
{
    protected $table = 'documents_requis_par_regime';

    protected $fillable = ['regime_id', 'libelle', 'obligatoire', 'ordre'];

    protected function casts(): array
    {
        return ['obligatoire' => 'boolean', 'ordre' => 'integer'];
    }

    public function regime(): BelongsTo
    {
        return $this->belongsTo(Regime::class);
    }
}
