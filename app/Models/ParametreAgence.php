<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParametreAgence extends Model
{
    protected $table = 'parametres_agence';

    protected $fillable = ['agence_id', 'cle', 'valeur', 'groupe'];

    protected function casts(): array
    {
        return ['valeur' => 'array'];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    /** Lit un paramètre d'agence avec repli sur la valeur par défaut. */
    public static function valeur(int $agenceId, string $cle, mixed $defaut = null): mixed
    {
        $parametre = static::where('agence_id', $agenceId)->where('cle', $cle)->first();

        return $parametre?->valeur ?? ($defaut ?? (static::valeursParDefaut()[$cle] ?? null));
    }

    /** Valeurs par défaut de tous les paramètres (clé => valeur). */
    public static function valeursParDefaut(): array
    {
        return [
            // Notifications et alertes
            'alertes_seuil_j2' => true,
            'alertes_seuil_j1' => true,
            'alertes_seuil_j0' => true,
            'alertes_canal_app' => true,
            'alertes_canal_email' => true,
            'alertes_destinataires' => ['transitaire', 'administrateur'],
            'alertes_heure_envoi' => '07:00',

            // Facturation
            'facture_mention_tva' => true,
            'facture_delai_paiement' => 30,
            'facture_pied_page' => 'Merci de votre confiance. Règlement par virement ou Mobile Money.',

            // Sécurité
            'securite_longueur_mot_de_passe' => 10,
            'securite_expiration_invitation' => 7,
            'securite_inactivite_minutes' => 120,

            // Notifications hors périmètre (préférences seulement)
            'canal_whatsapp_actif' => false,
            'canal_sms_actif' => false,
        ];
    }
}
