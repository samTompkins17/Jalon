<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\Conteneur;
use App\Models\Cout;
use App\Models\Dossier;
use App\Models\Facture;
use App\Models\JournalActivite;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $agenceId = $request->user()->agence_id;

        $periodes = [
            'jour' => ['du jour', 'à hier', today()->startOfDay(), today()->endOfDay(), today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'semaine' => ['de la semaine', 'à la semaine dernière', now()->startOfWeek(), now()->endOfWeek(), now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'mois' => ['du mois', 'au mois dernier', now()->startOfMonth(), now()->endOfMonth(), now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'trimestre' => ['du trimestre', 'au trimestre dernier', now()->startOfQuarter(), now()->endOfQuarter(), now()->subQuarter()->startOfQuarter(), now()->subQuarter()->endOfQuarter()],
        ];

        $periode = array_key_exists((string) $request->query('periode'), $periodes)
            ? (string) $request->query('periode')
            : 'mois';

        [$libellePeriode, $comparaison, $debut, $fin, $debutPrecedent, $finPrecedent] = $periodes[$periode];

        $caPeriode = $this->chiffreAffaires($agenceId, $debut, $fin);
        $caPrecedent = $this->chiffreAffaires($agenceId, $debutPrecedent, $finPrecedent);

        $variation = $caPrecedent > 0
            ? (($caPeriode - $caPrecedent) / $caPrecedent) * 100
            : null;

        $mois = collect(range(5, 0))
            ->map(fn (int $retour) => now()->startOfMonth()->subMonths($retour));

        $labelsMois = $mois->map(fn (Carbon $date) => Str::ucfirst($date->translatedFormat('M')))->all();

        $dossiersParMois = $mois->map(fn (Carbon $date) => Dossier::where('agence_id', $agenceId)
            ->whereBetween('created_at', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
            ->count())->all();

        $caParMois = $mois->map(fn (Carbon $date) => round($this->chiffreAffaires($agenceId, $date->copy()->startOfMonth(), $date->copy()->endOfMonth()) / 1_000_000, 2))->all();

        $coutsParMois = $mois->map(fn (Carbon $date) => Cout::whereHas('dossier', fn ($requete) => $requete->where('agence_id', $agenceId))
            ->whereBetween('date', [$date->copy()->startOfMonth()->toDateString(), $date->copy()->endOfMonth()->toDateString()])
            ->sum('montant'))->all();

        $margeParMois = collect($caParMois)
            ->map(fn (float $ca, int $index) => round($ca - ($coutsParMois[$index] / 1_000_000), 2))
            ->all();

        return view('tableau-de-bord', [
            'periode' => $periode,
            'libellePeriode' => $libellePeriode,
            'comparaison' => $comparaison,
            'caPeriode' => $caPeriode,
            'variation' => $variation,
            'labelsMois' => $labelsMois,
            'dossiersParMois' => $dossiersParMois,
            'caParMois' => $caParMois,
            'margeParMois' => $margeParMois,
            'echeances' => $this->echeances($agenceId),
            'factureProche' => Facture::query()
                ->where('agence_id', $agenceId)
                ->whereIn('statut', ['impayee', 'partielle'])
                ->with('client')
                ->orderBy('date_echeance')
                ->first(),
            'alertesImportantes' => $this->alertesImportantes($agenceId),
            'derniersDossiers' => $this->derniersDossiers($agenceId),
            'journal' => $this->journal($agenceId),
        ]);
    }

    private function chiffreAffaires(int $agenceId, Carbon $debut, Carbon $fin): float
    {
        return (float) Facture::where('agence_id', $agenceId)
            ->whereBetween('date_facture', [$debut->toDateString(), $fin->toDateString()])
            ->sum('total');
    }

    /** @return Collection<int, Conteneur> */
    private function echeances(int $agenceId): Collection
    {
        return Conteneur::query()
            ->where('statut', 'au_port')
            ->whereHas('dossier', fn ($requete) => $requete->where('agence_id', $agenceId))
            ->with(['dossier.client'])
            ->whereNotNull('fin_franchise')
            ->orderBy('fin_franchise')
            ->limit(3)
            ->get();
    }

    /** @return Collection<int, Alerte> */
    private function alertesImportantes(int $agenceId): Collection
    {
        return Alerte::query()
            ->where('agence_id', $agenceId)
            ->where('lue', false)
            ->with('dossier')
            ->orderByRaw("FIELD(gravite, 'critique', 'haute', 'moyenne', 'basse')")
            ->orderByDesc('date_alerte')
            ->limit(3)
            ->get();
    }

    /** @return Collection<int, Dossier> */
    private function derniersDossiers(int $agenceId): Collection
    {
        return Dossier::query()
            ->where('agence_id', $agenceId)
            ->where('brouillon', false)
            ->with('client')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();
    }

    /** @return Collection<int, JournalActivite> */
    private function journal(int $agenceId): Collection
    {
        return JournalActivite::query()
            ->where('agence_id', $agenceId)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();
    }
}
