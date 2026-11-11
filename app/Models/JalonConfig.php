<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class JalonConfig extends Model
{
    protected $table = 'jalons_config';

    protected $fillable = ['ordre', 'nom', 'condition_passage', 'visible_client'];

    protected function casts(): array
    {
        return ['visible_client' => 'boolean', 'ordre' => 'integer'];
    }

    /** Cache mémoire : la configuration ne change qu'à la sauvegarde des paramètres. */
    protected static ?Collection $cache = null;

    public static function tous(): Collection
    {
        return self::$cache ??= self::orderBy('ordre')->get();
    }

    public static function oublierCache(): void
    {
        self::$cache = null;
    }

    /** Valeurs par défaut, utilisées par le seeder et le bouton « rétablir ». */
    public static function valeursParDefaut(): array
    {
        return [
            1 => ['nom' => 'Dossier créé', 'condition' => 'Fiche client complète et référence de dossier générée.'],
            2 => ['nom' => 'Documents transmis', 'condition' => 'Toutes les pièces obligatoires du régime ont été téléversées (B/L, facture, colisage).'],
            3 => ['nom' => 'ECTN / BESC', 'condition' => 'Numéro ECTN/BESC obtenu auprès du GUCE ou du Conseil des chargeurs.'],
            4 => ['nom' => 'Manifeste déposé', 'condition' => 'Manifeste de la cargaison déposé et validé par le transporteur.'],
            5 => ['nom' => 'Déclaration soumise', 'condition' => 'Déclaration en détail soumise dans SYDONIA World et acceptée.'],
            6 => ['nom' => 'Inspection', 'condition' => 'Inspection douanière ou phytosanitaire réalisée au quai.'],
            7 => ['nom' => 'Droits à payer', 'condition' => 'Droits et taxes liquidés et acquittés à la recette des douanes.'],
            8 => ['nom' => 'BAE', 'condition' => 'Bon à enlever délivré par la douane.'],
            9 => ['nom' => 'Livré', 'condition' => 'Marchandise livrée et conteneur restitué au port.'],
        ];
    }

    public function getNomCompletAttribute(): string
    {
        return $this->ordre.'. '.$this->nom;
    }
}
