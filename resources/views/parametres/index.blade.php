@extends('layouts.app')

@section('titre', 'Paramètres')

@section('sous-titre', 'Configuration de l\'agence')

@section('fil')
    <a href="{{ route('tableau-de-bord') }}">Tableau de bord</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">Paramètres</span>
@endsection

@section('contenu')
    @php
        $valeur = fn (string $cle, $defaut = null) => $parametres[$cle] ?? $defaut;
    @endphp

    <div class="grid gap-5 lg:grid-cols-[260px_minmax(0,1fr)]">
        {{-- Menu des sections --}}
        <nav class="carte" aria-label="Sections des paramètres">
            <div class="carte-corps" style="padding:10px">
                @foreach ($sections as $cle => $libelle)
                    <a href="{{ route('parametres.index', ['section' => $cle]) }}"
                       @class(['menu-section', 'actif' => $section === $cle])
                       @if ($section === $cle) aria-current="page" @endif>
                        <span>{{ $libelle }}</span>
                        <span aria-hidden="true">›</span>
                    </a>
                @endforeach
            </div>
        </nav>

        <div class="grid gap-5 content-start">
            @if ($section === 'agence')
                <form method="POST" action="{{ route('parametres.update') }}" enctype="multipart/form-data" class="grid gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="agence">

                    <x-carte titre="Identité de l'agence" sous-titre="Informations légales et logo pour vos factures">
                        <div class="mb-4 flex items-center gap-4">
                            @if ($agence->logo_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($agence->logo_path) }}"
                                     alt="Logo de l'agence" style="width:56px;height:56px;border-radius:12px;object-fit:cover">
                            @else
                                <span style="display:grid;place-items:center;width:56px;height:56px;border-radius:12px;background:var(--c-brand);color:#fff;font-weight:700">
                                    {{ mb_substr($agence->nom, 0, 1) }}
                                </span>
                            @endif

                            <div>
                                <x-champ nom="logo" label="Logo officiel" type="file" accept="image/png,image/jpeg,image/webp" />
                                <p class="aide text-[12px] text-muted">PNG, JPG ou WebP. Taille maximale 2 Mo.</p>
                            </div>
                        </div>

                        <div class="groupe-champs">
                            <x-champ nom="nom" label="Nom de l'agence" requis :valeur="$agence->nom" />
                            <x-champ nom="numero_agrement" label="N° d'agrément en douane" :valeur="$agence->numero_agrement" />
                            <x-champ nom="email" label="E-mail" type="email" requis :valeur="$agence->email" />
                            <x-champ nom="telephone" label="Téléphone" requis :valeur="$agence->telephone" />
                            <x-champ nom="adresse" label="Adresse physique" :valeur="$agence->adresse" style="grid-column:1/-1" />
                            <x-champ nom="ville" label="Ville" requis :valeur="$agence->ville" />
                            <x-champ nom="pays" label="Pays" requis :valeur="$agence->pays" />
                        </div>
                    </x-carte>

                    <div class="flex flex-wrap gap-2">
                        <x-bouton type="submit">Enregistrer les modifications</x-bouton>
                    </div>
                </form>
            @endif

            @if ($section === 'transit')
                <form method="POST" action="{{ route('parametres.update') }}" class="grid gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="transit">

                    <x-carte titre="Paramètres de transit" sous-titre="Devise, franchise et numérotation des dossiers">
                        <div class="groupe-champs">
                            <x-champ nom="devise" label="Devise" requis :valeur="$agence->devise ?? 'FCFA'" />
                            <x-champ nom="delai_franchise_defaut" label="Franchise par défaut (jours)" type="number" min="1" max="30" requis :valeur="$agence->delai_franchise_defaut" />
                            <x-champ nom="dossier_prefixe" label="Préfixe des dossiers" requis :valeur="$agence->dossier_prefixe" />
                            <x-champ nom="dossier_annee" label="Année de numérotation" type="number" min="2020" max="2100" requis :valeur="$agence->dossier_annee" />
                        </div>

                        <p class="aide mt-3 text-[12px] text-muted">
                            Prochain dossier : <strong>{{ $apercuDossier }}</strong>.
                            La franchise s'applique aux conteneurs au port : J-2, J-1 puis J0.
                        </p>
                    </x-carte>

                    <div class="flex flex-wrap gap-2">
                        <x-bouton type="submit">Enregistrer les modifications</x-bouton>
                    </div>
                </form>
            @endif

            @if ($section === 'documents')
                <x-carte titre="Documents et formalités" sous-titre="Pièces exigées par régime douanier">
                    <div class="tableau-scroll">
                        <table class="tableau">
                            <thead>
                                <tr>
                                    <th scope="col">Régime</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Pièces requises</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($regimes as $regime)
                                    <tr>
                                        <td class="font-medium">{{ $regime->nom }}</td>
                                        <td class="cellule-compacte">{{ $regime->code }}</td>
                                        <td class="text-[12.5px] text-muted">{{ $regime->documentsRequis->pluck('libelle')->implode(', ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <p class="aide mt-3 text-[12px] text-muted">
                        Les pièces sont contrôlées à l'ajout de documents sur un dossier. La liste se modifie en base
                        tant que l'écran d'édition des régimes n'est pas livré.
                    </p>
                </x-carte>
            @endif

            @if ($section === 'jalons')
                <x-carte titre="Workflow et jalons" sous-titre="Les neuf étapes du processus douanier de Lomé">
                    <div class="grid gap-2">
                        @foreach ($jalons as $jalon)
                            <div class="ligne-info">
                                <div class="min-w-0">
                                    <p class="titre">{{ $jalon->ordre }}. {{ $jalon->nom }}</p>
                                    <p class="detail">{{ $jalon->condition_passage }}</p>
                                </div>
                                <x-badge :variante="$jalon->visible_client ? 'vert' : null">
                                    {{ $jalon->visible_client ? 'Visible client' : 'Interne' }}
                                </x-badge>
                            </div>
                        @endforeach
                    </div>

                    <p class="aide mt-3 text-[12px] text-muted">
                        Le passage d'un jalon est à sens unique ; seul un administrateur peut revenir en arrière avec un motif.
                    </p>
                </x-carte>
            @endif

            @if ($section === 'notifications')
                <form method="POST" action="{{ route('parametres.update') }}" class="grid gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="notifications">

                    <x-carte titre="Notifications et alertes" sous-titre="Seuils de surestarie et canaux d'envoi">
                        <x-bascule nom="alertes_seuil_j2" intitule="Alerte J-2" precision="Rappel préventif deux jours avant la fin de franchise." :actif="$valeur('alertes_seuil_j2', true)" />
                        <x-bascule nom="alertes_seuil_j1" intitule="Alerte J-1" precision="Notification la veille de l'échéance." :actif="$valeur('alertes_seuil_j1', true)" />
                        <x-bascule nom="alertes_seuil_j0" intitule="Alerte J0" precision="Alerte critique le jour de la fin de franchise." :actif="$valeur('alertes_seuil_j0', true)" />
                        <x-bascule nom="alertes_canal_app" intitule="Notifications dans l'application" precision="Bandeau d'alertes et compteur de la barre latérale." :actif="$valeur('alertes_canal_app', true)" />
                        <x-bascule nom="alertes_canal_email" intitule="Notifications par e-mail" precision="Envoi au client et au déclarant." :actif="$valeur('alertes_canal_email', true)" />

                        <div class="groupe-champs mt-4">
                            <x-champ nom="alertes_heure_envoi" label="Heure d'envoi quotidienne" type="time" :valeur="$valeur('alertes_heure_envoi', '07:00')" />
                        </div>

                        <p class="aide mt-2 text-[12px] text-muted">
                            La génération est planifiée par <code>php artisan jalon:alertes</code>. Les seuils désactivés
                            ne créent pas d'alerte.
                        </p>
                    </x-carte>

                    <div class="flex flex-wrap gap-2">
                        <x-bouton type="submit">Enregistrer les modifications</x-bouton>
                    </div>
                </form>
            @endif

            @if ($section === 'facturation')
                <form method="POST" action="{{ route('parametres.update') }}" class="grid gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="facturation">

                    <x-carte titre="Facturation" sous-titre="TVA, numérotation et mentions des factures">
                        <div class="groupe-champs">
                            <x-champ nom="tva_taux" label="TVA (%)" type="number" min="0" max="30" step="0.01" requis :valeur="$agence->tva_taux" />
                            <x-champ nom="facture_prefixe" label="Préfixe des factures" requis :valeur="$agence->facture_prefixe" />
                            <x-champ nom="facture_annee" label="Année de numérotation" type="number" min="2020" max="2100" requis :valeur="$agence->facture_annee" />
                            <x-champ nom="facture_delai_paiement" label="Délai de paiement (jours)" type="number" min="0" max="120" requis :valeur="$valeur('facture_delai_paiement', 30)" />
                        </div>

                        <p class="aide mt-2 text-[12px] text-muted">
                            Prochaine facture : <strong>{{ $apercuFacture }}</strong>.
                        </p>

                        <div class="mt-4">
                            <x-bascule nom="facture_mention_tva" intitule="Afficher la mention TVA" precision="Affiche le taux et le montant de TVA sur la facture imprimée." :actif="$valeur('facture_mention_tva', true)" />

                            <x-champ-zone nom="facture_pied_page" label="Pied de page des factures" :valeur="$valeur('facture_pied_page')" />
                        </div>
                    </x-carte>

                    <div class="flex flex-wrap gap-2">
                        <x-bouton type="submit">Enregistrer les modifications</x-bouton>
                    </div>
                </form>
            @endif

            @if ($section === 'securite')
                <form method="POST" action="{{ route('parametres.update') }}" class="grid gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section" value="securite">

                    <x-carte titre="Sécurité et accès" sous-titre="Règles appliquées aux comptes de l'agence">
                        <div class="groupe-champs">
                            <x-champ nom="securite_longueur_mot_de_passe" label="Longueur minimale du mot de passe" type="number" min="8" max="64" requis :valeur="$valeur('securite_longueur_mot_de_passe', 10)" />
                            <x-champ nom="securite_expiration_invitation" label="Validité d'une invitation (jours)" type="number" min="1" max="30" requis :valeur="$valeur('securite_expiration_invitation', 7)" />
                            <x-champ nom="securite_inactivite_minutes" label="Déconnexion après inactivité (minutes)" type="number" min="15" max="1440" requis :valeur="$valeur('securite_inactivite_minutes', 120)" />
                        </div>

                        <p class="aide mt-2 text-[12px] text-muted">
                            Le portail client est en lecture seule : aucun document ni dossier ne peut y être modifié.
                            Les rôles internes restent contrôlés côté serveur à chaque requête.
                        </p>
                    </x-carte>

                    <div class="flex flex-wrap gap-2">
                        <x-bouton type="submit">Enregistrer les modifications</x-bouton>
                    </div>
                </form>
            @endif

            @if ($section === 'integrations')
                <x-carte titre="Intégrations" sous-titre="Canaux de communication de l'agence">
                    <div class="grid gap-2">
                        <div class="ligne-info">
                            <div class="min-w-0">
                                <p class="titre">E-mail transactionnel</p>
                                <p class="detail">Invitations, alertes de surestarie et réinitialisations. Journalisé en local.</p>
                            </div>
                            <x-badge variante="vert">Actif</x-badge>
                        </div>

                        <div class="ligne-info">
                            <div class="min-w-0">
                                <p class="titre">WhatsApp Business</p>
                                <p class="detail">Notifications aux clients : préparation des numéros et des modèles de messages.</p>
                            </div>
                            <x-badge>À venir</x-badge>
                        </div>

                        <div class="ligne-info">
                            <div class="min-w-0">
                                <p class="titre">SMS</p>
                                <p class="detail">Repli pour les clients sans smartphone : non connecté.</p>
                            </div>
                            <x-badge>À venir</x-badge>
                        </div>
                    </div>

                    <p class="aide mt-3 text-[12px] text-muted">
                        Ces canaux se configurent dans les paramètres de notifications dès qu'un fournisseur est retenu.
                    </p>
                </x-carte>
            @endif
        </div>
    </div>
@endsection
