<?php

namespace App\Services;

use App\Models\Agence;
use App\Models\Client;
use App\Models\Dossier;
use App\Models\Facture;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Génération des références métier : JL-2027-0031, FA-2027-0012, CLI-2027-001
 * et des codes de suivi publics (ex. K7M2-9QX4).
 */
class ReferenceService
{
    /** Longueur de la partie incrémentale par type de document. */
    private const LONGUEURS = ['dossier' => 4, 'facture' => 4, 'client' => 3];

    /** Table et colonne qui portent la référence, pour éviter les doublons. */
    private const REFERENCES = [
        'dossier' => [Dossier::class, 'reference'],
        'facture' => [Facture::class, 'numero'],
        'client' => [Client::class, 'code'],
    ];

    public function dossier(Agence $agence): string
    {
        return $this->prochain($agence, 'dossier');
    }

    public function facture(Agence $agence): string
    {
        return $this->prochain($agence, 'facture');
    }

    public function client(Agence $agence): string
    {
        return $this->prochain($agence, 'client');
    }

    /**
     * Aperçu du prochain numéro sans consommer l'incrément (Paramètres > Facturation).
     */
    public function apercu(Agence $agence, string $type): string
    {
        [$numero] = $this->suivantLibre($agence, $type, (int) $agence->{"{$type}_increment"});

        return $numero;
    }

    /**
     * Prochaine référence non utilisée pour ce type, en sautant les numéros
     * déjà présents (import, reprise de données, incrément désynchronisé).
     *
     * @return array{0: string, 1: int} La référence et l'incrément à enregistrer.
     */
    private function suivantLibre(Agence $agence, string $type, int $increment): array
    {
        [$modele, $champ] = self::REFERENCES[$type];
        $longueur = self::LONGUEURS[$type] ?? 4;
        $prefixe = $agence->{"{$type}_prefixe"};
        $annee = $agence->{"{$type}_annee"};

        // Les références sont uniques par agence : le verrou posé sur la ligne
        // de l'agence suffit à écarter deux créations simultanées, et la
        // vérification rattrape un incrément désynchronisé (import, échec).
        do {
            $numero = $this->formater($prefixe, $annee, $increment, $longueur);
            $pris = $modele::query()
                ->where('agence_id', $agence->getKey())
                ->where($champ, $numero)
                ->exists();

            if ($pris) {
                $increment++;
            }
        } while ($pris);

        return [$numero, $increment + 1];
    }

    private function prochain(Agence $agence, string $type): string
    {
        return DB::transaction(function () use ($agence, $type) {
            // Verrou pessimiste : deux créations simultanées ne partagent pas le même numéro.
            /** @var Agence $verrouillee */
            $verrouillee = Agence::whereKey($agence->getKey())->lockForUpdate()->firstOrFail();

            $champIncrement = "{$type}_increment";
            [$numero, $incrementSuivant] = $this->suivantLibre($verrouillee, $type, (int) $verrouillee->{$champIncrement});

            $verrouillee->{$champIncrement} = $incrementSuivant;
            $verrouillee->save();

            // On répercute la valeur à jour sur l'instance appelante.
            $agence->{$champIncrement} = $verrouillee->{$champIncrement};

            return $numero;
        });
    }

    private function formater(string $prefixe, int $annee, int $increment, int $longueur): string
    {
        return sprintf('%s-%d-%s', $prefixe, $annee, str_pad((string) $increment, $longueur, '0', STR_PAD_LEFT));
    }

    /**
     * Code de suivi public : 8 caractères en deux groupes (K7M2-9QX4),
     * sans caractères ambigus (0/O, 1/I).
     */
    public function codeSuivi(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $code = substr($code, 0, 4).'-'.substr($code, 4, 4);
        } while (Dossier::withTrashed()->where('code_suivi', $code)->exists());

        return $code;
    }

    /** Identifiant d'agence unique à partir du nom (création d'espace). */
    public function slugAgence(string $nom): string
    {
        $base = Str::slug($nom) ?: 'agence';
        $slug = $base;
        $i = 2;

        while (Agence::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
