<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeDemo extends Model
{
    protected $table = 'demandes_demo';

    protected $fillable = [
        'nom', 'agence', 'email', 'telephone', 'format', 'creneau_at', 'message',
    ];

    protected function casts(): array
    {
        return ['creneau_at' => 'datetime'];
    }

    public function libelleFormat(): string
    {
        return $this->format === 'presentiel' ? 'Présentiel à Lomé' : 'Visioconférence';
    }
}
