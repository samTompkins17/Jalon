<?php

namespace App\Http\Controllers;

use App\Models\JalonConfig;
use App\Models\ParametreAgence;
use App\Models\Regime;
use App\Services\JournalService;
use App\Services\ReferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ParametresController extends Controller
{
    /** Sections disponibles dans la configuration de l'agence. */
    public const SECTIONS = [
        'agence' => 'Informations de l\'agence',
        'transit' => 'Paramètres de transit',
        'documents' => 'Documents et formalités',
        'jalons' => 'Workflow et jalons',
        'notifications' => 'Notifications et alertes',
        'facturation' => 'Facturation',
        'securite' => 'Sécurité et accès',
        'integrations' => 'Intégrations',
    ];

    public function index(Request $request): View
    {
        $this->autoriser($request);

        $section = (string) $request->query('section', 'agence');

        if (! array_key_exists($section, self::SECTIONS)) {
            $section = 'agence';
        }

        $agence = $request->user()->agence;
        $parametres = ParametreAgence::where('agence_id', $agence->id)->get()->pluck('valeur', 'cle')->all();

        return view('parametres.index', [
            'section' => $section,
            'sections' => self::SECTIONS,
            'agence' => $agence,
            'parametres' => $parametres,
            'jalons' => JalonConfig::tous(),
            'regimes' => Regime::with('documentsRequis')->orderBy('ordre')->get(),
            'apercuFacture' => (new ReferenceService)->apercu($agence, 'facture'),
            'apercuDossier' => (new ReferenceService)->apercu($agence, 'dossier'),
        ]);
    }

    public function update(Request $request, JournalService $journal): RedirectResponse
    {
        $this->autoriser($request);

        $section = (string) $request->input('section', 'agence');
        $agence = $request->user()->agence;

        match ($section) {
            'agence' => $this->enregistrerAgence($request, $agence),
            'transit' => $this->enregistrerTransit($request, $agence),
            'notifications' => $this->enregistrerParametres($request, $agence, 'alertes'),
            'facturation' => $this->enregistrerFacturation($request, $agence),
            'securite' => $this->enregistrerParametres($request, $agence, 'securite'),
            default => null,
        };

        $journal->enregistrer(
            'Paramètres',
            'Section « '.(self::SECTIONS[$section] ?? $section).' » mise à jour.',
            null,
            $request->user(),
        );

        return redirect()
            ->route('parametres.index', ['section' => $section])
            ->with('succes', 'Paramètres enregistrés.');
    }

    // ------------------------------------------------------------------ Sections

    private function enregistrerAgence(Request $request, $agence): void
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'numero_agrement' => ['nullable', 'string', 'max:60'],
            'email' => ['required', 'email', 'max:150'],
            'telephone' => ['required', 'string', 'max:40'],
            'adresse' => ['nullable', 'string', 'max:180'],
            'ville' => ['required', 'string', 'max:80'],
            'pays' => ['required', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'nom.required' => 'Renseignez le nom de l\'agence.',
            'email.required' => 'Renseignez une adresse e-mail de contact.',
            'telephone.required' => 'Renseignez un numéro de téléphone.',
            'ville.required' => 'Renseignez la ville.',
            'pays.required' => 'Renseignez le pays.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne doit pas dépasser 2 Mo.',
        ]);

        if ($request->hasFile('logo')) {
            $ancien = $agence->logo_path;
            $donnees['logo_path'] = $request->file('logo')->store('logos', 'public');

            if ($ancien) {
                Storage::disk('public')->delete($ancien);
            }
        }

        unset($donnees['logo']);

        $agence->update($donnees);
    }

    private function enregistrerTransit(Request $request, $agence): void
    {
        $donnees = $request->validate([
            'devise' => ['required', 'string', 'max:10'],
            'delai_franchise_defaut' => ['required', 'integer', 'min:1', 'max:30'],
            'dossier_prefixe' => ['required', 'string', 'max:10'],
            'dossier_annee' => ['required', 'integer', 'min:2020', 'max:2100'],
        ], [
            'delai_franchise_defaut.required' => 'Renseignez le délai de franchise par défaut.',
            'dossier_prefixe.required' => 'Renseignez le préfixe des dossiers.',
        ]);

        $agence->update($donnees);
    }

    private function enregistrerFacturation(Request $request, $agence): void
    {
        $donnees = $request->validate([
            'tva_taux' => ['required', 'numeric', 'min:0', 'max:30'],
            'facture_prefixe' => ['required', 'string', 'max:10'],
            'facture_annee' => ['required', 'integer', 'min:2020', 'max:2100'],
            'facture_delai_paiement' => ['required', 'integer', 'min:0', 'max:120'],
        ], [
            'tva_taux.required' => 'Renseignez le taux de TVA.',
            'facture_prefixe.required' => 'Renseignez le préfixe des factures.',
        ]);

        $agence->update(collect($donnees)->only(['tva_taux', 'facture_prefixe', 'facture_annee'])->all());

        $this->poser($agence->id, 'facture_delai_paiement', (int) $donnees['facture_delai_paiement'], 'facturation');
        $this->poser($agence->id, 'facture_mention_tva', $request->boolean('facture_mention_tva'), 'facturation');
        $this->poser($agence->id, 'facture_pied_page', (string) $request->input('facture_pied_page', ''), 'facturation');
    }

    private function enregistrerParametres(Request $request, $agence, string $groupe): void
    {
        if ($groupe === 'alertes') {
            foreach (['alertes_seuil_j2', 'alertes_seuil_j1', 'alertes_seuil_j0', 'alertes_canal_app', 'alertes_canal_email'] as $cle) {
                $this->poser($agence->id, $cle, $request->boolean($cle), 'alertes');
            }

            $this->poser($agence->id, 'alertes_heure_envoi', (string) $request->input('alertes_heure_envoi', '07:00'), 'alertes');

            return;
        }

        $this->poser($agence->id, 'securite_longueur_mot_de_passe', max(8, (int) $request->input('securite_longueur_mot_de_passe', 10)), 'securite');
        $this->poser($agence->id, 'securite_expiration_invitation', max(1, (int) $request->input('securite_expiration_invitation', 7)), 'securite');
        $this->poser($agence->id, 'securite_inactivite_minutes', max(15, (int) $request->input('securite_inactivite_minutes', 120)), 'securite');
    }

    private function poser(int $agenceId, string $cle, mixed $valeur, string $groupe): void
    {
        ParametreAgence::updateOrCreate(
            ['agence_id' => $agenceId, 'cle' => $cle],
            ['valeur' => $valeur, 'groupe' => $groupe],
        );
    }

    private function autoriser(Request $request): void
    {
        abort_unless($request->user()->peutGererParametres(), 403, 'Accès réservé aux administrateurs.');
    }
}
