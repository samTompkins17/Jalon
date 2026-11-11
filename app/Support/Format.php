<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Petites fonctions de formatage partagées (montants FCFA, dates françaises).
 */
class Format
{
    /** 9120000 => « 9 120 000 » (espaces insécables fines). */
    public static function nombre(int|float|null $valeur, int $decimales = 0): string
    {
        $valeur = (float) ($valeur ?? 0);

        $formate = number_format($valeur, $decimales, ',', ' ');

        // number_format insère des espaces simples : on les rend insécables.
        return str_replace(' ', "\u{00A0}", $formate);
    }

    public static function fcfa(int|float|null $valeur, bool $devise = true): string
    {
        $texte = self::nombre($valeur);

        return $devise ? $texte.' FCFA' : $texte;
    }

    public static function date(?CarbonInterface $date, string $format = 'j M Y'): string
    {
        return $date ? $date->translatedFormat($format) : '—';
    }

    public static function dateCourte(?CarbonInterface $date): string
    {
        return self::date($date, 'j M');
    }

    public static function dateHeure(?CarbonInterface $date): string
    {
        return $date ? $date->translatedFormat('j M Y à H:i') : '—';
    }

    public static function aujourdhui(): Carbon
    {
        return Carbon::today();
    }

    /** « Afi Mensah » => « Afi M. » (format des tableaux). */
    public static function nomCourt(?string $nom): string
    {
        $mots = preg_split('/\s+/', trim((string) $nom)) ?: [];
        $mots = array_values(array_filter($mots));

        if (count($mots) === 0) {
            return '—';
        }

        if (count($mots) === 1) {
            return $mots[0];
        }

        return $mots[0].' '.mb_strtoupper(mb_substr(end($mots), 0, 1)).'.';
    }

    public static function initiales(string $nom): string
    {
        $mots = preg_split('/\s+/', trim($nom)) ?: [];
        $mots = array_values(array_filter($mots));

        if (count($mots) === 0) {
            return '?';
        }

        if (count($mots) === 1) {
            return mb_strtoupper(mb_substr($mots[0], 0, 2));
        }

        return mb_strtoupper(mb_substr($mots[0], 0, 1).mb_substr($mots[count($mots) - 1], 0, 1));
    }

    /** « 1 jour », « 3 jours », « Dépassée ». */
    public static function jours(int $jours): string
    {
        return $jours <= 0 ? 'Dépassée' : $jours.' '.($jours > 1 ? 'jours' : 'jour');
    }
}
