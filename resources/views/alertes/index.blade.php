@extends('layouts.app')

@section('titre', 'Alertes')

@section('sous-titre')
    {{ $compteurs['non_lues'] }} non lue{{ $compteurs['non_lues'] > 1 ? 's' : '' }}
@endsection

@section('actions')
    @if ($compteurs['non_lues'] > 0)
        <form method="POST" action="{{ route('alertes.tout-lu') }}">
            @csrf
            <x-bouton type="submit" variante="contour">Tout marquer comme lu</x-bouton>
        </form>
    @endif
@endsection

@section('contenu')
    @php
        $filtres = [
            'toutes' => 'Toutes',
            'non_lues' => 'Non lues',
            'critiques' => 'Critiques',
        ];
    @endphp

    <div class="grid gap-5">
        <div class="segmented" role="group" aria-label="Filtrer les alertes" style="width:max-content;max-width:100%">
            @foreach ($filtres as $cle => $libelle)
                <a href="{{ route('alertes.index', $cle === 'toutes' ? [] : ['filtre' => $cle]) }}"
                   @class(['actif' => $filtre === $cle])
                   @if ($filtre === $cle) aria-current="page" @endif>
                    {{ $libelle }} ({{ $compteurs[$cle] }})
                </a>
            @endforeach
        </div>

        <div>
            @forelse ($alertes as $alerte)
                @php
                    $couleur = match ($alerte->gravite->value) {
                        'critique' => 'rose',
                        'haute' => 'ambre',
                        'moyenne' => 'jaune',
                        default => null,
                    };

                    $cible = $alerte->conteneur
                        ? route('conteneurs.show', $alerte->conteneur)
                        : $alerte->lien;
                @endphp

                <div @class(['alerte-ligne', $alerte->gravite->value, 'lue' => $alerte->lue])>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="titre-alerte">{{ $alerte->titre }}</p>

                            @if ($alerte->lue)
                                <x-badge>Lue</x-badge>
                            @else
                                <x-badge variante="rose">Non lue</x-badge>
                            @endif

                            <x-badge :variante="$couleur">{{ $alerte->gravite->label() }}</x-badge>
                        </div>

                        <p class="meta-alerte">
                            {{ $alerte->description ?: $alerte->type->label() }}
                        </p>
                    </div>

                    <div class="actions-alerte">
                        <a href="{{ $cible }}">Ouvrir</a>
                        <span class="text-muted">·</span>

                        @if ($alerte->lue)
                            <form method="POST" action="{{ route('alertes.non-lue', $alerte) }}" class="inline">
                                @csrf
                                <button type="submit">Marquer non lue</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('alertes.lue', $alerte) }}" class="inline">
                                @csrf
                                <button type="submit">Marquer comme lue</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <x-carte>
                    <x-etat-vide
                        icone="alertes"
                        titre="Aucune alerte"
                        texte="{{ $filtre === 'toutes' ? 'Aucune alerte pour le moment : vos dossiers sont à jour.' : 'Aucune alerte ne correspond à ce filtre.' }}"
                    />
                </x-carte>
            @endforelse
        </div>

        @if ($alertes->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12.5px] text-muted">
                    {{ $alertes->firstItem() }} à {{ $alertes->lastItem() }} sur {{ $alertes->total() }} alerte{{ $alertes->total() > 1 ? 's' : '' }}
                </p>

                <x-pagination :paginateur="$alertes" />
            </div>
        @endif

        <p class="text-center text-[12.5px] text-muted">
            Les alertes de surestarie sont générées automatiquement chaque matin à J-2, J-1 et J0, sans doublon.
        </p>
    </div>
@endsection
