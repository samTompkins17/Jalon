<?php

namespace App\Services;

use App\Enums\EcheanceAlerte;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutConteneur;
use App\Mail\AlerteSurestarieMail;
use App\Models\Alerte;
use App\Models\Conteneur;
use App\Models\Notification;
use App\Models\ParametreAgence;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * ALERTE SURESTARIE (priorité absolue).
 * Génère une alerte J-2 (Moyenne), J-1 (Haute) et J0 (Critique) par conteneur,
 * sans doublon, puis notifie dans l'application et par e-mail.
 */
class AlerteService
{
    public const TITRES = [
        'J-2' => 'Franchise à J-2',
        'J-1' => 'Franchise à J-1',
        'J0' => 'Franchise dépassée',
    ];

    /** Retourne le nombre d'alertes nouvellement créées. */
    public function generer(bool $envoyerEmail = true): int
    {
        $creees = 0;

        $conteneurs = Conteneur::query()
            ->with(['dossier.agence', 'dossier.client', 'dossier.transitaire'])
            ->where('statut', StatutConteneur::AuPort)
            ->whereNotNull('fin_franchise')
            ->get();

        foreach ($conteneurs as $conteneur) {
            $echeance = EcheanceAlerte::depuisJoursRestants((int) $conteneur->jours_restants);

            if (! $echeance) {
                continue;
            }

            $dossier = $conteneur->dossier;

            if (! $dossier) {
                continue;
            }

            // La clé unique (conteneur_id, echeance) garantit l'absence de doublon.
            $alerte = Alerte::firstOrCreate(
                [
                    'conteneur_id' => $conteneur->id,
                    'echeance' => $echeance->value,
                ],
                [
                    'agence_id' => $dossier->agence_id,
                    'dossier_id' => $dossier->id,
                    'type' => 'franchise',
                    'titre' => self::TITRES[$echeance->value],
                    'description' => sprintf(
                        'Fin de franchise le %s · Dossier %s · %s',
                        $conteneur->fin_franchise?->translatedFormat('j M Y'),
                        $dossier->reference,
                        $dossier->client?->nom ?? 'Client'
                    ),
                    'cible' => 'Conteneur '.$conteneur->numero_formate,
                    'gravite' => $echeance->gravite()->value,
                    'lue' => false,
                    'date_alerte' => today(),
                    'statut_envoi' => 'en_attente',
                ]
            );

            if (! $alerte->wasRecentlyCreated) {
                continue;
            }

            $creees++;

            $this->notifier($alerte, $envoyerEmail);
        }

        return $creees;
    }

    /** Crée les notifications internes et envoie les e-mails configurés. */
    public function notifier(Alerte $alerte, bool $envoyerEmail = true): void
    {
        $agence = $alerte->agence;
        $parametres = $agence?->relationLoaded('parametres')
            ? $agence->parametres->pluck('valeur', 'cle')->all()
            : ParametreAgence::where('agence_id', $alerte->agence_id)->get()->pluck('valeur', 'cle')->all();

        $destinataires = $parametres['alertes_destinataires'] ?? ['transitaire', 'administrateur'];
        $destinataires = is_array($destinataires) ? $destinataires : (array) $destinataires;

        $canalApp = (bool) ($parametres['alertes_canal_app'] ?? true);
        $canalEmail = $envoyerEmail && (bool) ($parametres['alertes_canal_email'] ?? true);

        $utilisateurs = User::query()
            ->where('agence_id', $alerte->agence_id)
            ->whereIn('role', array_map(fn ($r) => $r instanceof RoleUtilisateur ? $r->value : $r, $destinataires))
            ->where('statut_activation', 'actif')
            ->get();

        if ($utilisateurs->isEmpty()) {
            $utilisateurs = User::query()
                ->where('agence_id', $alerte->agence_id)
                ->whereIn('role', ['administrateur', 'transitaire'])
                ->get();
        }

        $lien = '/alertes';

        foreach ($utilisateurs as $utilisateur) {
            if ($canalApp) {
                Notification::firstOrCreate(
                    ['user_id' => $utilisateur->id, 'alerte_id' => $alerte->id, 'canal' => 'app'],
                    [
                        'titre' => $alerte->titre,
                        'message' => $alerte->description ?? $alerte->titre,
                        'lien' => $lien,
                    ]
                );
            }

            if ($canalEmail && $utilisateur->email) {
                try {
                    Mail::to($utilisateur->email)->send(new AlerteSurestarieMail($alerte, $utilisateur));
                    Notification::firstOrCreate(
                        ['user_id' => $utilisateur->id, 'alerte_id' => $alerte->id, 'canal' => 'email'],
                        [
                            'titre' => $alerte->titre,
                            'message' => $alerte->description ?? $alerte->titre,
                            'lien' => $lien,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::warning('Échec de l\'envoi de l\'alerte surestarie', [
                        'alerte_id' => $alerte->id,
                        'user_id' => $utilisateur->id,
                        'erreur' => $e->getMessage(),
                    ]);
                }
            }
        }

        $alerte->forceFill([
            'statut_envoi' => $utilisateurs->isNotEmpty() ? 'envoyee' : 'echec',
            'envoyee_at' => $utilisateurs->isNotEmpty() ? now() : null,
        ])->save();
    }
}
