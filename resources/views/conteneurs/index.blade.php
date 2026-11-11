@extends('layouts.app')

@section('titre', 'Conteneurs')

@section('sous-titre')
    {{ $compteurs['au_port'] }} conteneur{{ $compteurs['au_port'] > 1 ? 's' : '' }} au port
@endsection

@section('contenu')
    @php
        $filtres = [
            'tous' => 'Tous',
            'au_port' => 'Au port',
            'sorti' => 'Sortis',
            'restitue' => 'Restitués',
        ];
    @endphp

    <div class="grid gap-5">
        <div class="segmented segmented--scrollable" role="group" aria-label="Filtrer par statut">
            @foreach ($filtres as $cle => $libelle)
                <a href="{{ route('conteneurs.index', array_filter(['statut' => $cle === 'tous' ? null : $cle, 'q' => $recherche ?: null])) }}"
                   @class(['actif' => $statut === $cle])
                   @if ($statut === $cle) aria-current="page" @endif>
                    {{ $libelle }} ({{ $compteurs[$cle] }})
                </a>
            @endforeach
        </div>

        <x-carte>
            <div class="tableau-scroll">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Numéro</th>
                            <th scope="col">Dossier</th>
                            <th scope="col">Type</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Fin de franchise</th>
                            <th scope="col" class="cellule-droite">Alerte</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conteneurs as $conteneur)
                            @php
                                $jours = $conteneur->jours_restants;
                                $echeance = $conteneur->echeance;
                            @endphp

                            <tr>
                                <td class="cellule-compacte">
                                    <a href="{{ route('conteneurs.show', $conteneur) }}" class="reference">
                                        {{ $conteneur->numero_formate }}
                                    </a>
                                </td>
                                <td class="cellule-compacte">
                                    @if ($conteneur->dossier)
                                        <a href="{{ route('dossiers.show', $conteneur->dossier) }}" class="hover:underline">
                                            {{ $conteneur->dossier->reference }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="cellule-compacte">{{ $conteneur->type }}</td>
                                <td>
                                    <x-badge :variante="match ($conteneur->statut->value) {
                                        'au_port' => 'bleu',
                                        'restitue' => 'vert',
                                        default => null,
                                    }">{{ $conteneur->statut->label() }}</x-badge>
                                </td>
                                <td class="cellule-compacte" @if ($conteneur->estEnSurestarie()) style="color: var(--c-rose);font-weight:600" @endif>
                                    {{ $conteneur->etat_franchise }}
                                </td>
                                <td class="cellule-droite">
                                    @if ($echeance)
                                        <x-badge :variante="match ($echeance->value) {
                                            'J0' => 'rose',
                                            'J-1' => 'ambre',
                                            default => 'jaune',
                                        }">{{ $echeance->value === 'J0' ? 'J0' : $echeance->value }}</x-badge>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-etat-vide
                                        icone="conteneurs"
                                        titre="Aucun conteneur ne correspond"
                                        texte="{{ $recherche !== '' ? 'Modifiez votre recherche ou revenez à la liste complète.' : 'Les conteneurs ajoutés à un dossier apparaîtront ici.' }}"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($conteneurs->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color: var(--c-line-soft)">
                    <p class="text-[12.5px] text-muted">
                        {{ $conteneurs->firstItem() }} à {{ $conteneurs->lastItem() }} sur {{ $conteneurs->total() }} conteneur{{ $conteneurs->total() > 1 ? 's' : '' }}
                    </p>

                    <x-pagination :paginateur="$conteneurs" />
                </div>
            @endif
        </x-carte>

        <p class="text-center text-[12.5px] text-muted">
            La franchise départage les jours calendaires offerts par la ligne maritime avant surestarie.
            Les alertes J-2, J-1 et J0 sont générées chaque matin.
        </p>
    </div>
@endsection
