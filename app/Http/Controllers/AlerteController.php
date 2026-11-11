<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlerteController extends Controller
{
    /** Centre d'alertes : surestaries, documents, paiements et arrivées. */
    public function index(Request $request): View
    {
        $this->autoriser($request);

        $filtre = (string) $request->query('filtre', 'toutes');
        $agenceId = $request->user()->agence_id;

        $base = Alerte::query()->deLAgence($agenceId);

        $compteurs = [
            'toutes' => (clone $base)->count(),
            'non_lues' => (clone $base)->nonLues()->count(),
            'critiques' => (clone $base)->critiques()->count(),
        ];

        $requete = (clone $base)->with(['dossier.client', 'conteneur']);

        match ($filtre) {
            'non_lues' => $requete->nonLues(),
            'critiques' => $requete->critiques(),
            default => $filtre = 'toutes',
        };

        $alertes = $requete
            ->orderBy('lue')
            ->orderByRaw("CASE gravite WHEN 'critique' THEN 0 WHEN 'haute' THEN 1 WHEN 'moyenne' THEN 2 ELSE 3 END")
            ->orderByDesc('date_alerte')
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();

        return view('alertes.index', [
            'alertes' => $alertes,
            'compteurs' => $compteurs,
            'filtre' => $filtre,
        ]);
    }

    public function marquerLue(Request $request, Alerte $alerte): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $alerte);

        $alerte->marquerLue();

        return back()->with('statut', 'Alerte marquée comme lue.');
    }

    public function marquerNonLue(Request $request, Alerte $alerte): RedirectResponse
    {
        $this->autoriser($request);
        $this->verifierAgence($request, $alerte);

        $alerte->marquerNonLue();

        return back()->with('statut', 'Alerte marquée comme non lue.');
    }

    public function toutMarquerLu(Request $request): RedirectResponse
    {
        $this->autoriser($request);

        $nombre = Alerte::query()
            ->deLAgence($request->user()->agence_id)
            ->nonLues()
            ->update(['lue' => true, 'lue_at' => now()]);

        return back()->with('statut', $nombre > 0
            ? $nombre.' alerte'.($nombre > 1 ? 's ont' : ' a').' été marquée'.($nombre > 1 ? 's' : '').' comme lue'.($nombre > 1 ? 's' : '').'.'
            : 'Toutes les alertes étaient déjà lues.');
    }

    private function autoriser(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role->value, ['administrateur', 'transitaire', 'comptable'], true),
            403,
        );
    }

    private function verifierAgence(Request $request, Alerte $alerte): void
    {
        abort_unless($alerte->agence_id === $request->user()->agence_id, 404);
    }
}
