@extends('layouts.app')

@section('titre-onglet', 'Dossier '.$dossier->reference)

@section('titre')
    Dossier <span style="color: var(--c-brand)">{{ $dossier->reference }}</span>
@endsection

@section('sous-titre')
    {{ $dossier->client?->nom }} · {{ $dossier->regime?->nom }} · Port de Lomé
@endsection

@section('fil')
    <a href="{{ route('dossiers.index') }}">Dossiers</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">{{ $dossier->reference }}</span>
@endsection

@section('actions')
    <div class="flex flex-wrap items-center gap-2">
        @if (auth()->user()->permission('modifier_dossiers') || auth()->user()->estAdministrateur())
            <x-bouton href="{{ route('dossiers.edit', $dossier) }}" variante="contour">Modifier le dossier</x-bouton>
        @endif

        @if ($dossier->jalon_actuel < 9)
            <x-bouton type="button" data-modale-ouvrir="#modale-jalon">
                Passer au jalon {{ $dossier->jalon_actuel + 1 }}
            </x-bouton>
        @endif
    </div>
@endsection

@section('contenu')
    @php
        $onglets = [
            'apercu' => 'Aperçu',
            'documents' => 'Documents',
            'conteneurs' => 'Conteneurs',
            'couts' => 'Coûts',
            'facture' => 'Facture',
            'historique' => 'Historique',
            'alertes' => 'Alertes',
        ];

        $compteurs = [
            'documents' => $dossier->documents->count(),
            'conteneurs' => $dossier->conteneurs->count(),
            'couts' => $dossier->couts->count(),
            'facture' => $dossier->factures->count(),
            'historique' => $dossier->historique->count(),
            'alertes' => $dossier->alertes->count(),
        ];
    @endphp

    <div class="grid gap-5">
        <nav class="onglets" aria-label="Sections du dossier">
            @foreach ($onglets as $cle => $libelle)
                <a
                    href="{{ route('dossiers.show', ['dossier' => $dossier, 'onglet' => $cle]) }}"
                    @class(['onglet', 'actif' => $onglet === $cle])
                    @if ($onglet === $cle) aria-current="page" @endif
                >
                    {{ $libelle }}
                    @if (! empty($compteurs[$cle]))
                        <span class="compte">{{ $compteurs[$cle] }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        @if ($dossier->brouillon)
            <div class="bandeau bandeau--info">
                <x-icone nom="info" :taille="16" />
                <span>
                    Ce dossier est un brouillon : il n'apparaît pas dans le suivi public et peut encore être complété.
                </span>
            </div>
        @endif

        @include('dossiers.partiels.'.$onglet)
    </div>

    @if ($dossier->jalon_actuel < 9)
        <x-modale
            id="modale-jalon"
            :titre="'Passer au jalon '.($dossier->jalon_actuel + 1)"
            :sous-titre="$dossier->reference.' · '.($jalonSuivant?->nom ?? 'Jalon suivant')"
        >
            <form method="POST" action="{{ route('dossiers.jalon', $dossier) }}">
                @csrf

                @if ($dossier->jalon_actuel + 1 === 9)
                    <p class="mb-3"><x-badge variante="vert">Dernier jalon</x-badge></p>
                @endif

                <div class="bandeau bandeau--succes mb-4">
                    <x-icone nom="check" :taille="16" />
                    <span>
                        Condition de passage :
                        {{ $jalonSuivant?->condition_passage ?? 'Vérifiez que les étapes précédentes sont terminées.' }}
                    </span>
                </div>

                <x-champ
                    nom="date_passage"
                    label="Date de passage"
                    type="date"
                    :valeur="today()->format('Y-m-d')"
                    requis
                />

                <x-champ-zone
                    nom="commentaire"
                    label="Commentaire (facultatif)"
                    placeholder="Ex. Livraison effectuée aux entrepôts du client à Akodésséwa, bon de livraison émis…"
                />

                <div class="modale-pied">
                    <x-bouton type="button" variante="contour" data-modale-fermer>Annuler</x-bouton>
                    <x-bouton type="submit" icone="check">Confirmer le passage</x-bouton>
                </div>
            </form>
        </x-modale>
    @endif
@endsection
