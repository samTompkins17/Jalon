<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'alerte_id', 'canal', 'titre', 'message', 'lien', 'lue', 'lu_at',
    ];

    protected function casts(): array
    {
        return ['lue' => 'boolean', 'lu_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alerte(): BelongsTo
    {
        return $this->belongsTo(Alerte::class);
    }

    public function scopeNonLues(Builder $query): Builder
    {
        return $query->where('lue', false);
    }
}
