@extends('layouts.app')

@section('titre', 'Dossiers')

@section('sous-titre')
    {{ $total }} dossier{{ $total > 1 ? 's' : '' }} enregistré{{ $total > 1 ? 's' : '' }}
@endsection

@section('actions')
    @if (auth()->user()->permission('creer_dossiers'))
        <x-bouton href="{{ route('dossiers.creation') }}">Nouveau dossier</x-bouton>
    @endif
@endsection

@section('contenu')
    <div class="grid gap-5">
        <div class="carte">
            <div class="carte-corps">
                <form method="GET" action="{{ route('dossiers.index') }}" class="barre-filtres">
                    @if ($recherche !== '')
                        <input type="hidden" name="q" value="{{ $recherche }}">
                    @endif

                    <label class="filtre">
                        <span class="filtre-label">État :</span>
                        <select name="etat" data-envoi-auto>
                            @foreach (['tous' => 'Tous les états', 'en_cours' => 'En cours', 'en_retard' => 'En retard', 'livre' => 'Livré', 'brouillon' => 'Brouillon'] as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected((string) $etat === (string) $valeur)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="filtre">
                        <span class="filtre-label">Période :</span>
                        <select name="periode" data-envoi-auto>
                            @foreach (['7' => '7 derniers jours', '30' => '30 derniers jours', '90' => '90 derniers jours', '365' => '12 derniers mois', 'tous' => 'Toutes les périodes'] as $valeur => $libelle)
                                <option value="{{ $valeur }}" @selected((string) $periode === (string) $valeur)>{{ $libelle }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="filtre">
                        <span class="filtre-label">Responsable :</span>
                        <select name="responsable" data-envoi-auto>
                            <option value="tous" @selected($responsable === 'tous')>Tous</option>
                            @foreach ($responsables as $membre)
                                <option value="{{ $membre->id }}" @selected((string) $responsable === (string) $membre->id)>{{ $membre->nom }}</option>
                            @endforeach
                        </select>
                    </label>

                    @if ($recherche !== '' || $etat !== 'tous' || $periode !== '30' || $responsable !== 'tous')
                        <a href="{{ route('dossiers.index') }}" class="lien-action">Réinitialiser</a>
                    @endif
                </form>
            </div>
        </div>

        <x-carte>
            <div class="tableau-scroll">
<table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Référence</th>
                        <th scope="col">Client</th>
                        <th scope="col">Créé le</th>
                        <th scope="col">Responsable</th>
                        <th scope="col">Jalon</th>
                        <th scope="col">Franchise</th>
                        <th scope="col">État</th>
                        <th scope="col">Marge (FCFA)</th>
                        <th scope="col" class="cellule-droite">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dossiers as $dossier)
                        @php
                            $jours = $dossier->conteneurUrgent()?->jours_restants;
                        @endphp

                        <tr>
                            <td class="cellule-compacte">
                                <a href="{{ route('dossiers.show', $dossier) }}" class="reference">{{ $dossier->reference }}</a>
                            </td>
                            <td class="cellule-client">{{ $dossier->client?->nom }}</td>
                            <td class="cellule-compacte">{{ $dossier->created_at?->translatedFormat('j M') }}</td>
                            <td>{{ \App\Support\Format::nomCourt($dossier->transitaire?->nom) }}</td>
                            <td class="cellule-compacte">{{ $dossier->jalon_actuel }} sur 9</td>
                            <td>
                                @if ($jours === null)
                                    <span class="text-muted">—</span>
                                @elseif ($jours <= 0)
                                    <x-badge variante="rose">J0</x-badge>
                                @elseif ($jours === 1)
                                    <x-badge variante="ambre">J-1</x-badge>
                                @elseif ($jours === 2)
                                    <x-badge variante="jaune">J-2</x-badge>
                                @else
                                    <span class="text-muted">{{ $jours }} j</span>
                                @endif
                            </td>
                            <td>
                                <x-badge :variante="match ($dossier->etat) {
                                    'livre' => 'vert',
                                    'en_retard' => 'rose',
                                    'brouillon' => null,
                                    default => 'bleu',
                                }">{{ $dossier->etat_label }}</x-badge>
                            </td>
                            <td class="cellule-compacte">
                                {{ $dossier->factures_count > 0 ? \App\Support\Format::nombre($dossier->marge_reelle) : '—' }}
                            </td>
                            <td class="cellule-droite">
                                <span class="actions-ligne">
                                    <a href="{{ route('dossiers.show', $dossier) }}">Consulter</a>
                                    <span class="point-separateur">·</span>
                                    @if (auth()->user()->permission('modifier_dossiers') || auth()->user()->estAdministrateur())
                                        <a href="{{ route('dossiers.edit', $dossier) }}">Modifier</a>
                                    @else
                                        <span class="text-muted">Modifier</span>
                                    @endif
                                    <span class="point-separateur">·</span>
                                    <form method="POST" action="{{ route('dossiers.archiver', $dossier) }}"
                                          data-confirmer="Archiver le dossier {{ $dossier->reference }} ? Il restera consultable par un administrateur."
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="hover:underline">Archiver</button>
                                    </form>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-etat-vide icone="dossiers" titre="Aucun dossier ne correspond" texte="Modifiez les filtres ou créez un nouveau dossier." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
</div>

            @if ($dossiers->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color: var(--c-line-soft)">
                    <p class="text-[12.5px] text-muted">
                        {{ $dossiers->firstItem() }} à {{ $dossiers->lastItem() }} sur {{ $dossiers->total() }} dossier{{ $dossiers->total() > 1 ? 's' : '' }}
                    </p>

                    <x-pagination :paginateur="$dossiers" />
                </div>
            @endif
        </x-carte>

        <p class="text-center text-[12.5px] text-muted">
            Données de démonstration : noms, numéros et montants sont fictifs.
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-envoi-auto]').forEach((champ) => {
            champ.addEventListener('change', () => champ.form.requestSubmit());
        });
    </script>
@endpush
