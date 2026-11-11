<?php

use App\Http\Controllers\ActivationController;
use App\Http\Controllers\AdministrationPlateformeController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConteneurController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DossierController;
use App\Http\Controllers\EquipeController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\MotDePasseController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\PortailController;
use App\Http\Controllers\ProfilController;
use App\Http\Middleware\AdministrationPlateforme;
use App\Http\Middleware\AgenceActive;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $utilisateur = Auth::user();

    return $utilisateur instanceof User && $utilisateur->estSuperAdmin()
        ? redirect()->route('plateforme.index')
        : redirect()->route('tableau-de-bord');
});

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'create'])->name('connexion');
    Route::post('/connexion', [AuthController::class, 'store'])->name('connexion.store');

    Route::get('/inscription', [InscriptionController::class, 'create'])->name('inscription');
    Route::post('/inscription', [InscriptionController::class, 'store'])->name('inscription.store');
    Route::post('/inscription/agence', [InscriptionController::class, 'demanderAgence'])->name('inscription.agence');

    Route::get('/mot-de-passe-oublie', [MotDePasseController::class, 'demande'])->name('mot-de-passe.demande');
    Route::post('/mot-de-passe-oublie', [MotDePasseController::class, 'envoyer'])->name('mot-de-passe.envoyer');
    Route::get('/reinitialiser-mot-de-passe/{token}', [MotDePasseController::class, 'formulaire'])->name('mot-de-passe.formulaire');
    Route::post('/reinitialiser-mot-de-passe', [MotDePasseController::class, 'reinitialiser'])->name('mot-de-passe.reinitialiser');

    Route::get('/invitation/{token}', [ActivationController::class, 'show'])->name('activation.show');
    Route::post('/invitation/{token}', [ActivationController::class, 'store'])->name('activation.store');
});

// Suivi public par code (sans compte).
Route::get('/suivi/{code?}', [PortailController::class, 'suivi'])->name('suivi');

Route::post('/deconnexion', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('deconnexion');

Route::middleware(['auth', AdministrationPlateforme::class])
    ->prefix('plateforme')
    ->name('plateforme.')
    ->group(function () {
        Route::get('/', [AdministrationPlateformeController::class, 'index'])->name('index');
        Route::post('/demandes/{demande}/approuver', [AdministrationPlateformeController::class, 'approuver'])->name('demandes.approuver');
        Route::post('/demandes/{demande}/refuser', [AdministrationPlateformeController::class, 'refuser'])->name('demandes.refuser');
        Route::post('/agences/{agence}/statut', [AdministrationPlateformeController::class, 'changerStatutAgence'])->name('agences.statut');
    });

Route::middleware(['auth', AgenceActive::class])->group(function () {
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('tableau-de-bord');

    // Profil et préférences.
    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::post('/profil/photo', [ProfilController::class, 'updatePhoto'])->name('profil.photo');
    Route::put('/profil/mot-de-passe', [ProfilController::class, 'updatePassword'])->name('profil.mot-de-passe');
    Route::put('/profil/preferences', [ProfilController::class, 'updatePreferences'])->name('profil.preferences');

    // Dossiers.
    Route::get('/dossiers', [DossierController::class, 'index'])->name('dossiers.index');
    Route::get('/dossiers/creer', [DossierController::class, 'create'])->name('dossiers.creation');
    Route::post('/dossiers', [DossierController::class, 'store'])->name('dossiers.store');
    Route::get('/dossiers/{dossier}', [DossierController::class, 'show'])->name('dossiers.show');
    Route::get('/dossiers/{dossier}/modifier', [DossierController::class, 'edit'])->name('dossiers.edit');
    Route::put('/dossiers/{dossier}', [DossierController::class, 'update'])->name('dossiers.update');
    Route::delete('/dossiers/{dossier}', [DossierController::class, 'destroy'])->name('dossiers.archiver');
    Route::post('/dossiers/{dossier}/jalon', [DossierController::class, 'avancerJalon'])->name('dossiers.jalon');
    Route::post('/dossiers/{dossier}/jalon/recul', [DossierController::class, 'reculerJalon'])->name('dossiers.jalon.recul');
    Route::post('/dossiers/{dossier}/documents', [DossierController::class, 'ajouterDocument'])->name('dossiers.documents.store');
    Route::post('/dossiers/{dossier}/documents/{document}/valider', [DossierController::class, 'validerDocument'])->name('dossiers.documents.valider');
    Route::delete('/dossiers/{dossier}/documents/{document}', [DossierController::class, 'supprimerDocument'])->name('dossiers.documents.destroy');
    Route::get('/dossiers/{dossier}/documents/{document}/telecharger', [DossierController::class, 'telechargerDocument'])->name('dossiers.documents.telecharger');
    Route::post('/dossiers/{dossier}/conteneurs', [DossierController::class, 'ajouterConteneur'])->name('dossiers.conteneurs.store');
    Route::post('/dossiers/{dossier}/couts', [DossierController::class, 'ajouterCout'])->name('dossiers.couts.store');
    Route::delete('/dossiers/{dossier}/couts/{cout}', [DossierController::class, 'supprimerCout'])->name('dossiers.couts.destroy');

    // Exports et rapports.
    Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');
    Route::get('/exports/{type}/csv', [ExportController::class, 'csv'])->name('exports.csv');

    // Paramètres de l'agence.
    Route::get('/parametres', [ParametresController::class, 'index'])->name('parametres.index');
    Route::put('/parametres', [ParametresController::class, 'update'])->name('parametres.update');

    // Facturation.
    Route::get('/facturation', [FactureController::class, 'index'])->name('facturation.index');
    Route::get('/facturation/creer', [FactureController::class, 'create'])->name('facturation.creation');
    Route::post('/facturation', [FactureController::class, 'store'])->name('facturation.store');
    Route::get('/facturation/{facture}', [FactureController::class, 'show'])->name('facturation.show');
    Route::get('/facturation/{facture}/imprimable', [FactureController::class, 'imprimable'])->name('facturation.imprimable');
    Route::post('/facturation/{facture}/paiement', [FactureController::class, 'paiement'])->name('facturation.paiement');

    // Clients.
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/creer', [ClientController::class, 'create'])->name('clients.creation');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/modifier', [ClientController::class, 'edit'])->name('clients.edit');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'archiver'])->name('clients.archiver');
    Route::post('/clients/{client}/invitation', [ClientController::class, 'renvoyerInvitation'])->name('clients.invitation');

    // Alertes.
    Route::get('/alertes', [AlerteController::class, 'index'])->name('alertes.index');
    Route::post('/alertes/tout-lu', [AlerteController::class, 'toutMarquerLu'])->name('alertes.tout-lu');
    Route::post('/alertes/{alerte}/lue', [AlerteController::class, 'marquerLue'])->name('alertes.lue');
    Route::post('/alertes/{alerte}/non-lue', [AlerteController::class, 'marquerNonLue'])->name('alertes.non-lue');

    // Conteneurs.
    Route::get('/conteneurs', [ConteneurController::class, 'index'])->name('conteneurs.index');
    Route::get('/conteneurs/{conteneur}', [ConteneurController::class, 'show'])->name('conteneurs.show');
    Route::post('/conteneurs/{conteneur}/statut', [ConteneurController::class, 'updateStatut'])->name('conteneurs.statut');

    // Équipe et invitations.
    Route::get('/equipe', [EquipeController::class, 'index'])->name('equipe.index');
    Route::get('/equipe/inviter', [EquipeController::class, 'create'])->name('equipe.inviter');
    Route::post('/equipe/inviter', [EquipeController::class, 'store'])->name('equipe.invitation.store');
    Route::post('/equipe/invitations/{invitation}/renvoyer', [EquipeController::class, 'renvoyer'])->name('equipe.invitation.renvoyer');
    Route::delete('/equipe/invitations/{invitation}', [EquipeController::class, 'annuler'])->name('equipe.invitation.annuler');
    Route::post('/equipe/membres/{membre}/statut', [EquipeController::class, 'changerStatut'])->name('equipe.membre.statut');
    Route::post('/equipe/membres/{membre}/role', [EquipeController::class, 'changerRole'])->name('equipe.membre.role');

    // Portail client (lecture seule).
    Route::get('/portail', [PortailController::class, 'index'])->name('portail.tableau-de-bord');
    Route::get('/portail/dossiers/{dossier}', [PortailController::class, 'dossier'])->name('portail.dossier');
    Route::get('/portail/dossiers/{dossier}/documents/{document}', [PortailController::class, 'document'])->name('portail.document');
});
