<?php

namespace App\Providers;

use App\Models\Alerte;
use App\Models\Conteneur;
use App\Models\Dossier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'fr'));
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'fr');

        View::composer('layouts.app', function ($vue) {
            $utilisateur = auth()->user();

            if (! $utilisateur) {
                return $vue->with('compteurs', []);
            }

            $agenceId = $utilisateur->agence_id;

            $vue->with('compteurs', [
                'dossiers' => Dossier::where('agence_id', $agenceId)->count(),
                'alertes' => Alerte::where('agence_id', $agenceId)->where('lue', false)->count(),
                'conteneurs' => Conteneur::whereHas('dossier', fn ($requete) => $requete->where('agence_id', $agenceId))
                    ->where('statut', 'au_port')
                    ->count(),
            ]);
        });
    }
}
