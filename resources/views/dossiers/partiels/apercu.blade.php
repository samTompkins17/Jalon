@php
    $facture = $dossier->factures->sortByDesc('date_facture')->first();
    $conteneurUrgent = $dossier->conteneurUrgent();
    $conteneurCritique = $dossier->conteneurCritique();
    $jours = $conteneurUrgent?->jours_restants;
    $typeConteneur = $dossier->conteneurs->first()?->type ?? 'conteneur';
    $courtes = [1 => 'Créé', 2 => 'Docs', 3 => 'ECTN', 4 => 'Manif.', 5 => 'Décl.', 6 => 'Insp.', 7 => 'Droits', 8 => 'BAE', 9 => 'Livré'];
    $lienSuivi = route('portail.tableau-de-bord', ['code' => $dossier->code_suivi]);
@endphp

<div class="grid gap-5 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
    <div class="grid gap-5 content-start">
        {{-- Marge prévisionnelle --}}
        <div class="carte">
            <div class="carte-corps">
                <p class="etiquette">Marge prévisionnelle (estimée)</p>

                <div class="montant-ca" style="font-size: clamp(32px, 4vw, 48px); margin-top: 12px;">
                    {{ \App\Support\Format::nombre($dossier->marge_previsionnelle) }}
                    <span class="devise" style="font-size: clamp(18px, 2.4vw, 26px); color: var(--c-muted)">FCFA</span>
                </div>

                <p class="carte-sous-titre" style="margin-top: 10px;">
                    Calculé sur la grille tarifaire standard pour conteneur {{ $typeConteneur }}
                    en {{ mb_strtolower($dossier->regime?->nom ?? 'régime courant') }}.
                </p>
            </div>
        </div>

        {{-- Trois indicateurs --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="stat-mini">
                <p class="etiquette">Marge réelle</p>
                <p class="valeur">{{ \App\Support\Format::nombre($dossier->marge_reelle) }}<small>FCFA</small></p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Facturation</p>
                <p class="mt-2">
                    @if ($facture)
                        <x-badge :variante="match ($facture->statut->value) {
                            'payee' => 'vert',
                            'partielle' => 'jaune',
                            default => 'rose',
                        }">{{ $facture->statut->label() }}</x-badge>
                    @else
                        <x-badge>Non facturé</x-badge>
                    @endif
                </p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Documents</p>
                <p class="valeur">
                    {{ $documentsValides }} sur {{ $documentsRequis->count() }}
                    <small>validés</small>
                </p>
            </div>
        </div>

        {{-- Processus des 9 jalons --}}
        <x-carte etiquette="Processus douanier de Lomé" titre="Les 9 jalons">
            <x-slot:actions>
                @if ($dossier->jalon_actuel < 9)
                    <x-bouton type="button" taille="petit" data-modale-ouvrir="#modale-jalon">
                        Passer au jalon {{ $dossier->jalon_actuel + 1 }}
                    </x-bouton>
                @else
                    <x-badge variante="vert">Dossier livré</x-badge>
                @endif
            </x-slot:actions>

            <div class="jalons-processus">
                @foreach ($jalons as $jalon)
                    @php
                        $passee = $jalon->ordre < $dossier->jalon_actuel;
                        $actuelle = $jalon->ordre === $dossier->jalon_actuel;
                    @endphp

                    <div @class(['jalon-etape', 'actuelle' => $actuelle])>
                        <span @class(['jalon-pastille', 'fait' => $passee, 'actuelle' => $actuelle])>
                            @if ($passee)
                                <x-icone nom="check" :taille="15" />
                            @else
                                {{ $jalon->ordre }}
                            @endif
                        </span>
                        <span class="nom">{{ $courtes[$jalon->ordre] ?? $jalon->nom }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 border-t pt-4" style="border-color: var(--c-line-soft)">
                <span class="legende"><span class="pastille" style="background: var(--c-surface-2)"></span> Gris et coché : terminé</span>
                <span class="legende"><span class="pastille" style="background: var(--c-brand)"></span> Orange : en cours</span>
                <span class="legende"><span class="pastille" style="border: 1px dashed var(--c-line-fort)"></span> Pointillés : à venir</span>
            </div>
        </x-carte>
    </div>

    <div class="grid gap-5 content-start">
        {{-- Alerte de franchise --}}
        @if ($conteneurCritique)
            <div class="carte-alerte">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="etiquette">Alerte quai critique</p>
                        <p class="titre-alerte">Franchise dépassée</p>
                        <p class="texte-alerte">Surestaries en cours au terminal à conteneurs.</p>
                    </div>
                    <span style="font-size: 30px; font-weight: 700; letter-spacing: -0.03em;">J0</span>
                </div>

                <div class="pied-alerte">
                    <span>Conteneur {{ $conteneurCritique->numero_formate }}</span>
                    <a href="{{ route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'conteneurs']) }}"
                       style="font-weight: 600; text-decoration: underline; text-underline-offset: 3px;">
                        Traiter en urgence
                    </a>
                </div>
            </div>
        @elseif ($jours !== null && $jours <= 2)
            <div class="carte-alerte carte-alerte--attention">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="etiquette">Surveillance franchise</p>
                        <p class="titre-alerte">Franchise bientôt échue</p>
                        <p class="texte-alerte">Le conteneur doit être enlevé dans {{ $jours }} jour{{ $jours > 1 ? 's' : '' }}.</p>
                    </div>
                    <span style="font-size: 30px; font-weight: 700; letter-spacing: -0.03em;">J-{{ $jours }}</span>
                </div>

                <div class="pied-alerte">
                    <span>Conteneur {{ $conteneurUrgent?->numero_formate }}</span>
                    <a href="{{ route('dossiers.show', ['dossier' => $dossier, 'onglet' => 'conteneurs']) }}"
                       style="font-weight: 600; text-decoration: underline; text-underline-offset: 3px;">
                        Voir le conteneur
                    </a>
                </div>
            </div>
        @else
            <div class="carte-alerte carte-alerte--ok">
                <p class="etiquette">Surveillance franchise</p>
                <p class="titre-alerte">Aucune alerte de franchise</p>
                <p class="texte-alerte">
                    {{ $dossier->conteneurs->isEmpty() ? 'Aucun conteneur rattaché au dossier.' : 'Les conteneurs sont sortis du port ou disposent encore de franchise.' }}
                </p>
            </div>
        @endif

        {{-- Statut d'avancement --}}
        <div class="carte">
            <div class="carte-corps">
                <div class="flex items-start justify-between gap-4">
                    <p class="etiquette">Statut d'avancement</p>
                    <span style="font-size: 27px; font-weight: 600; letter-spacing: -0.03em; color: var(--c-brand)">
                        {{ $dossier->jalon_actuel }} / 9
                    </span>
                </div>

                <h2 class="carte-titre">Jalon : {{ $dossier->jalon?->nom ?? 'Dossier créé' }}</h2>
                <p class="carte-sous-titre">
                    {{ $dossier->jalon_suivant?->condition_passage ?? 'Toutes les étapes douanières sont terminées.' }}
                </p>

                <div class="mt-4 flex items-center gap-2 border-t pt-4" style="border-color: var(--c-line-soft)">
                    <span class="pastille" style="width:9px;height:9px;border-radius:999px;background: {{ $jours !== null && $jours <= 0 ? 'var(--c-rose)' : 'var(--c-brand)' }}"></span>
                    <span class="text-[12.5px]" style="color: var(--c-ink-2)">
                        @if ($dossier->etat === 'livre')
                            Marchandise livrée, dossier clôturé
                        @elseif ($jours !== null && $jours <= 0)
                            En attente de retrait physique au port
                        @elseif ($dossier->brouillon)
                            Brouillon à compléter
                        @else
                            En cours de traitement douanier
                        @endif
                    </span>
                </div>
            </div>
        </div>

        {{-- Informations générales --}}
        <x-carte etiquette="Données du dossier" titre="Informations générales">
            <div class="ligne-donnee">
                <span class="label">Client</span>
                <span class="valeur">{{ $dossier->client?->nom }}</span>
            </div>
            <div class="ligne-donnee">
                <span class="label">Régime</span>
                <span class="valeur">{{ $dossier->regime?->nom }} ({{ $dossier->regime?->code }})</span>
            </div>
            <div class="ligne-donnee">
                <span class="label">Ligne maritime</span>
                <span class="valeur">{{ $dossier->ligne_maritime }}</span>
            </div>
            <div class="ligne-donnee">
                <span class="label">B/L (connaissement)</span>
                <span class="valeur">{{ $dossier->numero_bl ?: '—' }}</span>
            </div>
            <div class="ligne-donnee">
                <span class="label">Nature de marchandise</span>
                <span class="valeur">{{ $dossier->nature_marchandise }}</span>
            </div>
            @if ($dossier->pays_origine)
                <div class="ligne-donnee">
                    <span class="label">Pays d'origine</span>
                    <span class="valeur">{{ $dossier->pays_origine }}</span>
                </div>
            @endif
            <div class="ligne-donnee">
                <span class="label">Arrivée prévue</span>
                <span class="valeur">{{ $dossier->date_arrivee_prevue?->translatedFormat('j M Y') ?? '—' }}</span>
            </div>
            <div class="ligne-donnee">
                <span class="label">Code de suivi</span>
                <span class="valeur">
                    <button type="button" class="code-suivi" data-copier="{{ $dossier->code_suivi }}" title="Copier le code">
                        {{ $dossier->code_suivi }}
                        <x-icone nom="copier" :taille="12" />
                    </button>
                </span>
            </div>

            <div class="mt-4">
                <button type="button" class="lien-action" data-copier="{{ $lienSuivi }}">
                    Copier le lien de suivi public
                </button>
            </div>
        </x-carte>
    </div>
</div>
