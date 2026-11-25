@extends('layouts.app')

@php
    $agence = auth()->user()?->agence;
    $debutSemaine = now()->startOfWeek();
@endphp

@section('titre', 'Tableau de bord')

@section('sous-titre')
    {{ $agence?->nom ?? 'Agence de démonstration' }}, semaine du {{ $debutSemaine->translatedFormat('j F') }}
@endsection

@section('actions')
    <x-bouton href="{{ route('dossiers.creation') }}" icone="plus">Nouveau dossier</x-bouton>
@endsection

@section('contenu')
    <div class="grid gap-5">

        {{-- Période ------------------------------------------------------- --}}
        <div class="segmented w-fit" role="group" aria-label="Période analysée" data-anim="arrive">
            @foreach (['jour' => 'Jour', 'semaine' => 'Semaine', 'mois' => 'Mois', 'trimestre' => 'Trimestre'] as $cle => $libelle)
                <a
                    href="{{ route('tableau-de-bord', ['periode' => $cle]) }}"
                    @if ($periode === $cle) aria-current="page" @endif
                >{{ $libelle }}</a>
            @endforeach
        </div>

        {{-- Chiffre d'affaires -------------------------------------------- --}}
        <div class="carte-ca" data-anim="arrive" data-tilt>
            <div>
                <p class="etiquette" style="color: var(--c-ink-2)">Chiffre d'affaires {{ $libellePeriode }}</p>

                <div class="montant-ca">
                    <x-anim.counter :value="\App\Support\Format::nombre($caPeriode)" />
                    <span class="devise">FCFA</span>
                </div>

                @if ($variation !== null)
                    <p class="variation" @if ($variation < 0) style="color: var(--c-rose)" @endif>
                        <x-icone :nom="$variation < 0 ? 'fleche-bas' : 'fleche-haut'" :taille="14" />
                        {{ number_format(abs($variation), 1, ',', ' ') }} % par rapport {{ $comparaison }}
                    </p>
                @else
                    <p class="variation" style="color: var(--c-ink-2)">
                        Pas de période de référence pour la comparaison
                    </p>
                @endif
            </div>

            @php
                $caMaxi = max($barresCa12) ?: 1;
            @endphp
            <div class="mini-barres" aria-hidden="true">
                @foreach ($barresCa12 as $i => $caMois)
                    <span
                        @class(['mini-barre', 'actuelle' => $i === count($barresCa12) - 1])
                        style="height: {{ max(8, (int) round($caMois / $caMaxi * 100)) }}%"
                        title="{{ \App\Support\Format::fcfa($caMois) }}"
                    ></span>
                @endforeach
            </div>
        </div>

        {{-- Indicateurs clés ----------------------------------------------- --}}
        @php
            $kpis = [
                ['libelle' => 'Dossiers en cours', 'valeur' => $statsKpi['dossiers'], 'route' => 'dossiers.index', 'alerte' => false],
                ['libelle' => 'Conteneurs au port', 'valeur' => $statsKpi['conteneurs'], 'route' => 'conteneurs.index', 'alerte' => false],
                ['libelle' => 'Franchise échue ou J0', 'valeur' => $statsKpi['franchises'], 'route' => 'alertes.index', 'alerte' => true],
                ['libelle' => 'Factures à encaisser', 'valeur' => $statsKpi['factures'], 'route' => 'facturation.index', 'alerte' => false],
            ];
        @endphp
        <div class="grille-stats">
            @foreach ($kpis as $i => $kpi)
                <a
                    href="{{ route($kpi['route']) }}"
                    class="stat"
                    data-anim="arrive"
                    data-index="{{ $i }}"
                >
                    <span class="etiquette">{{ $kpi['libelle'] }}</span>
                    <p
                        class="valeur"
                        @if ($kpi['alerte'] && $kpi['valeur'] > 0) style="color: var(--c-rose)" @endif
                    ><x-anim.counter :value="$kpi['valeur']" /></p>
                </a>
            @endforeach
        </div>

        {{-- Graphiques ---------------------------------------------------- --}}
        <div class="grid gap-5 lg:grid-cols-2">
            <x-carte
                etiquette="Volume d'activité"
                titre="Évolution des dossiers"
                :sous-titre="'Dossiers ouverts par mois (' . mb_strtolower($labelsMois[0]) . ' – ' . mb_strtolower(end($labelsMois)) . ')'"
                data-anim="arrive"
                data-tilt
            >
                <div class="graphique">
                    <canvas id="graphique-dossiers" data-graphique aria-label="Nombre de dossiers ouverts par mois" role="img"></canvas>
                </div>
            </x-carte>

            <x-carte etiquette="Rentabilité" titre="Chiffre d'affaires et marge" sous-titre="En millions de FCFA" data-anim="arrive" data-tilt>
                <x-slot:actions>
                    <div class="flex items-center gap-4">
                        <span class="legende"><span class="pastille" style="background: var(--c-brand)"></span>CA</span>
                        <span class="legende"><span class="pastille" style="background: var(--c-teal)"></span>Marge</span>
                    </div>
                </x-slot:actions>

                <div class="graphique">
                    <canvas id="graphique-rentabilite" data-graphique aria-label="Chiffre d'affaires et marge par mois" role="img"></canvas>
                </div>
            </x-carte>
        </div>

        {{-- Échéances et alertes ------------------------------------------ --}}
        <div class="grid gap-5 lg:grid-cols-2">
            <x-carte etiquette="Calendrier opérationnel" titre="Prochaines échéances" data-anim="arrive" data-tilt>
                @forelse ($echeances as $conteneur)
                    @php
                        $jours = $conteneur->jours_restants;
                    @endphp

                    <div class="ligne-info">
                        <div class="min-w-0">
                            <p class="titre">Fin de franchise {{ $conteneur->numero_formate }}</p>
                            <p class="detail">
                                {{ $conteneur->dossier->reference }} · {{ $conteneur->dossier->client?->nom }}
                            </p>
                        </div>

                        <div class="droite">
                            @if ($jours !== null && $jours <= 0)
                                <x-badge variante="rose">J0</x-badge>
                            @elseif ($jours === 1)
                                <x-badge variante="ambre">J-1</x-badge>
                            @elseif ($jours === 2)
                                <x-badge variante="jaune">J-2</x-badge>
                            @else
                                <x-badge variante="jaune">
                                    {{ $conteneur->fin_franchise?->translatedFormat('j M') }}
                                </x-badge>
                            @endif
                        </div>
                    </div>
                @empty
                    <x-etat-vide icone="conteneurs" titre="Aucune franchise en cours" texte="Tous les conteneurs sont sortis ou restitués." />
                @endforelse

                @if ($factureProche)
                    <a href="{{ route('facturation.index') }}" class="ligne-info">
                        <div class="min-w-0">
                            <p class="titre">Échéance de la facture {{ $factureProche->numero }}</p>
                            <p class="detail">{{ $factureProche->client?->nom }} · {{ \App\Support\Format::fcfa($factureProche->solde) }} restant</p>
                        </div>

                        <div class="droite">
                            <x-badge variante="jaune">{{ $factureProche->date_echeance?->translatedFormat('j M') }}</x-badge>
                        </div>
                    </a>
                @endif
            </x-carte>

            <x-carte etiquette="Surveillance douane" titre="Alertes importantes" data-anim="arrive" data-tilt>
                <x-slot:actions>
                    <a href="{{ route('alertes.index') }}" class="lien-action">Voir toutes les alertes</a>
                </x-slot:actions>

                @forelse ($alertesImportantes as $alerte)
                    <div class="ligne-info">
                        <div class="min-w-0">
                            <p class="titre">{{ $alerte->titre }}</p>
                            <p class="detail">{{ $alerte->description }}</p>
                        </div>

                        <div class="droite">
                            <x-badge :variante="match ($alerte->gravite->value) {
                                'critique' => 'rose',
                                'haute' => 'ambre',
                                'moyenne' => 'jaune',
                                default => null,
                            }">{{ ucfirst($alerte->gravite->value) }}</x-badge>
                        </div>
                    </div>
                @empty
                    <x-etat-vide icone="alertes" titre="Aucune alerte active" texte="Les franchises et les documents sont à jour." />
                @endforelse
            </x-carte>
        </div>

        {{-- Dossiers récents et journal ----------------------------------- --}}
        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.45fr)_minmax(0,1fr)]">
            <x-carte etiquette="Suivi des opérations" titre="Derniers dossiers créés ou modifiés" data-anim="arrive" data-tilt>
                <div class="tableau-scroll">
<table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Client</th>
                            <th scope="col">Avancement</th>
                            <th scope="col" class="cellule-droite">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($derniersDossiers as $dossier)
                            <tr>
                                <td class="reference cellule-compacte">{{ $dossier->reference }}</td>
                                <td class="cellule-client">{{ $dossier->client?->nom }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <x-jalons :courant="$dossier->jalon_actuel" />
                                        <span class="jalon-legende">Jalon {{ $dossier->jalon_actuel }} sur 9</span>
                                    </div>
                                </td>
                                <td class="cellule-droite">
                                    <x-badge :variante="match ($dossier->etat) {
                                        'livre' => 'vert',
                                        'en_retard' => 'rose',
                                        'brouillon' => null,
                                        default => 'bleu',
                                    }">{{ $dossier->etat_label }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-etat-vide icone="dossiers" titre="Aucun dossier" texte="Créez votre premier dossier de transit." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
</div>
            </x-carte>

            <x-carte etiquette="Journal de bord" titre="Activités récentes" data-anim="arrive" data-tilt>
                @forelse ($journal as $entree)
                    <div class="journal-item">
                        <p>
                            <strong>{{ $entree->user?->nom }}</strong>
                            {{ $entree->description }}
                        </p>
                        <p class="journal-meta">
                            {{ $entree->dossier?->reference ?? 'Agence' }} ·
                            {{ $entree->created_at?->translatedFormat('j M') }}
                        </p>
                    </div>
                @empty
                    <x-etat-vide icone="horloge" titre="Aucune activité" texte="Les actions de l'équipe apparaîtront ici." />
                @endforelse
            </x-carte>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module">
        document.addEventListener('DOMContentLoaded', () => {
            if (! window.Jalon) {
                return;
            }

            const dossiers = @json($dossiersParMois);
            const labels = @json($labelsMois);

            window.Jalon.graphiqueDepuis(document.getElementById('graphique-dossiers'), {
                labels,
                series: [{ label: 'Dossiers', data: dossiers, couleur: '--c-slate' }],
                etiquettes: true,
                maximum: Math.max(...dossiers, 1) * 1.35,
                epaisseur: 46,
                arrondi: 8,
                suffixe: '',
            });

            window.Jalon.graphiqueDepuis(document.getElementById('graphique-rentabilite'), {
                labels,
                series: [
                    { label: 'CA', data: @json($caParMois), couleur: '--c-brand' },
                    { label: 'Marge', data: @json($margeParMois), couleur: '--c-teal' },
                ],
                groupe: true,
                maximum: Math.max(...@json($caParMois), 1) * 1.25,
                epaisseur: 24,
                arrondi: 6,
                suffixe: ' M FCFA',
            });
        });
    </script>
@endpush
