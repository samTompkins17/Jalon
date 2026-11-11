<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegimeFranchise extends Model
{
    protected $table = 'regimes_franchise';

    protected $fillable = ['regime_id', 'ligne_maritime', 'jours'];

    protected function casts(): array
    {
        return ['jours' => 'integer'];
    }

    public function regime(): BelongsTo
    {
        return $this->belongsTo(Regime::class);
    }
}
