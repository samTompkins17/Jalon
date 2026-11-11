<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvenementConteneur extends Model
{
    protected $table = 'evenements_conteneur';

    protected $fillable = ['conteneur_id', 'type', 'libelle', 'description', 'date_evenement', 'user_id'];

    protected function casts(): array
    {
        return ['date_evenement' => 'date'];
    }

    public function conteneur(): BelongsTo
    {
        return $this->belongsTo(Conteneur::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
