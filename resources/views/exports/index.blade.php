@extends('layouts.app')

@section('titre', 'Exports & Rapports')

@section('sous-titre', 'Données de l\'agence, exports CSV et journal d\'activité')

@section('contenu')
    @php
        $periodes = ['7' => '7 jours', '30' => '30 jours', '90' => '90 jours', '365' => '12 mois', 'tous' => 'Tout'];
    @endphp

    <div class="grid gap-5">
        <div class="segmented segmented--scrollable" role="group" aria-label="Période des rapports">
            @foreach ($periodes as $cle => $libelle)
                <a href="{{ route('exports.index', ['periode' => $cle]) }}"
                   @class(['actif' => $periode === $cle])
                   @if ($periode === $cle) aria-current="page" @endif>
                    {{ $libelle }}
                </a>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-mini">
                <p class="etiquette">Chiffre d'affaires</p>
                <p class="valeur">{{ \App\Support\Format::nombre($ca) }}<small>FCFA</small></p>
                <p class="text-[12px] text-muted mt-1">Facturé sur la période</p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Encaissé</p>
                <p class="valeur" style="color: var(--c-vert)">{{ \App\Support\Format::nombre($encaisse) }}<small>FCFA</small></p>
                <p class="text-[12px] text-muted mt-1">Règlements reçus</p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Reste à recouvrer</p>
                <p class="valeur" style="color: var(--c-rose)">{{ \App\Support\Format::nombre($impaye) }}<small>FCFA</small></p>
                <p class="text-[12px] text-muted mt-1">Solde des factures émises</p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Dossiers</p>
                <p class="valeur">{{ $nombreDossiers }}<small>sur la période</small></p>
                <p class="text-[12px] text-muted mt-1">
                    {{ $dossiersLivres }} livré{{ $dossiersLivres > 1 ? 's' : '' }} · {{ $conteneursAuPort }} conteneur{{ $conteneursAuPort > 1 ? 's' : '' }} au port
                </p>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
            <x-carte titre="Exports disponibles" sous-titre="Fichiers CSV séparés par « ; », lisibles dans Excel">
                <div class="grid gap-2">
                    @foreach ($types as $cle => $libelle)
                        <div class="ligne-info">
                            <div class="min-w-0">
                                <p class="titre">{{ $libelle }}</p>
                                <p class="detail">
                                    @switch($cle)
                                        @case('dossiers') Références, clients, jalons, états et marges. @break
                                        @case('factures') Totaux, règlements, soldes et statuts. @break
                                        @case('clients') Coordonnées et soldes dus. @break
                                        @case('conteneurs') Numéros, statuts et échéances de franchise. @break
                                        @default Les 2 000 dernières actions de l'agence. @break
                                    @endswitch
                                </p>
                            </div>

                            <x-bouton href="{{ route('exports.csv', ['type' => $cle, 'periode' => $periode]) }}"
                                      variante="contour" taille="petit" icone="telecharger">
                                CSV
                            </x-bouton>
                        </div>
                    @endforeach
                </div>

                <p class="aide mt-3 text-[12px] text-muted">
                    Les exports dossiers et factures suivent la période sélectionnée. Les autres exports couvrent
                    l'ensemble des données de l'agence.
                </p>
            </x-carte>

            <x-carte titre="Journal d'activité" sous-titre="Dernières actions enregistrées">
                <div class="tableau-scroll">
                    <table class="tableau">
                        <thead>
                            <tr>
                                <th scope="col">Date</th>
                                <th scope="col">Action</th>
                                <th scope="col">Utilisateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($journal as $entree)
                                <tr>
                                    <td class="cellule-compacte">{{ $entree->created_at?->translatedFormat('j M · H:i') }}</td>
                                    <td>
                                        <p class="font-medium">{{ $entree->action }}</p>
                                        <p class="text-[12px] text-muted">{{ $entree->description }}</p>
                                    </td>
                                    <td class="cellule-compacte">{{ \App\Support\Format::nomCourt($entree->user?->nom) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <x-etat-vide icone="exports" titre="Aucune activité" texte="Les actions de l'agence apparaîtront ici." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-carte>
        </div>
    </div>
@endsection
