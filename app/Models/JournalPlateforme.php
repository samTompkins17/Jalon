<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class JournalPlateforme extends Model
{
    protected $table = 'journal_plateforme';

    protected $fillable = [
        'user_id', 'action', 'cible_type', 'cible_id', 'cible_libelle', 'details',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre une action de la plateforme dans le journal.
     *
     * @param  string  $action  Code de l’action (ex. « demande_approuvee »).
     * @param  Model|null  $cible  Modèle concerné (optionnel).
     * @param  string|null  $details  Détail libre (motif, note…).
     */
    public static function enregistrer(string $action, ?Model $cible = null, ?string $details = null): self
    {
        $entree = [
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($cible !== null) {
            $entree['cible_type'] = Str::snake(class_basename($cible));
            $entree['cible_id'] = $cible->getKey();
            $entree['cible_libelle'] = Str::limit(
                (string) ($cible->nom_agence ?? $cible->nom ?? $cible->email ?? ''),
                150,
                ''
            ) ?: null;
        }

        return static::create($entree);
    }
}
