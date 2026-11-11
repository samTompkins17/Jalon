@extends('layouts.app')

@section('titre', 'Facturation')

@section('sous-titre', 'Factures et coûts par dossier')

@section('actions')
    @if (auth()->user()->peutFacturer() || auth()->user()->permission('facturer'))
        <x-bouton href="{{ route('facturation.creation') }}" icone="plus">Nouvelle facture</x-bouton>
    @endif
@endsection

@section('contenu')
    @php
        $filtres = [
            'toutes' => 'Toutes',
            'impayee' => 'Impayées',
            'partielle' => 'Partielles',
            'payee' => 'Payées',
        ];
    @endphp

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <div class="grid gap-4 content-start">
            <div class="segmented segmented--scrollable" role="group" aria-label="Filtrer les factures">
                @foreach ($filtres as $cle => $libelle)
                    <a href="{{ route('facturation.index', array_filter(['statut' => $cle === 'toutes' ? null : $cle, 'q' => $recherche ?: null])) }}"
                       @class(['actif' => $statut === $cle])
                       @if ($statut === $cle) aria-current="page" @endif>
                        {{ $libelle }} ({{ $compteurs[$cle] }})
                    </a>
                @endforeach
            </div>

            <x-carte titre="Factures" :sous-titre="$factures->total().' facture'.($factures->total() > 1 ? 's' : '')">
                <div class="tableau-scroll">
                    <table class="tableau">
                        <thead>
                            <tr>
                                <th scope="col">N°</th>
                                <th scope="col">Dossier</th>
                                <th scope="col">Client</th>
                                <th scope="col">État</th>
                                <th scope="col" class="cellule-droite">Échéance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($factures as $facture)
                                <tr @class(['ligne-selectionnee' => $selection?->id === $facture->id])>
                                    <td>
                                        <a href="{{ route('facturation.index', array_filter(['statut' => $statut === 'toutes' ? null : $statut, 'q' => $recherche ?: null, 'facture' => $facture->numero])) }}"
                                           class="reference" style="display:inline-flex;align-items:center;gap:8px">
                                            @if ($selection?->id === $facture->id)
                                                <span class="point" style="color: var(--c-brand)"></span>
                                            @endif
                                            {{ $facture->numero }}
                                        </a>
                                    </td>
                                    <td class="cellule-compacte">
                                        @if ($facture->dossier)
                                            <a href="{{ route('dossiers.show', $facture->dossier) }}" class="reference">{{ $facture->dossier->reference }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="cellule-client">{{ $facture->client?->nom }}</td>
                                    <td>
                                        <x-badge :variante="match ($facture->statut->value) {
                                            'payee' => 'vert',
                                            'partielle' => 'jaune',
                                            default => 'rose',
                                        }">{{ $facture->statut->label() }}</x-badge>
                                    </td>
                                    <td class="cellule-droite">
                                        {{ $facture->statut->value === 'payee' ? '—' : $facture->date_echeance?->translatedFormat('j M') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <x-etat-vide
                                            icone="facturation"
                                            titre="Aucune facture"
                                            texte="Créez une facture depuis un dossier ou avec le bouton Nouvelle facture."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($factures->hasPages())
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color: var(--c-line-soft)">
                        <p class="text-[12.5px] text-muted">
                            {{ $factures->firstItem() }} à {{ $factures->lastItem() }} sur {{ $factures->total() }} facture{{ $factures->total() > 1 ? 's' : '' }}
                        </p>

                        <x-pagination :paginateur="$factures" />
                    </div>
                @endif
            </x-carte>
        </div>

        @if ($selection)
            <x-carte class="fiche-conteneur">
                @include('facturation.partiels.detail', ['facture' => $selection])
            </x-carte>
        @endif
    </div>

    @if ($selection && $selection->solde > 0)
        <x-modale id="modale-paiement" titre="Enregistrer un paiement" :sous-titre="'Facture '.$selection->numero.' · solde restant '.\App\Support\Format::nombre($selection->solde).' FCFA'">
            <form method="POST" action="{{ route('facturation.paiement', $selection) }}">
                @csrf

                <div class="groupe-champs">
                    <x-champ nom="montant" label="Montant (FCFA)" type="number" requis :valeur="$selection->solde" min="1" step="1" />
                    <x-champ-select nom="mode" label="Mode" requis>
                        @foreach ($modes as $mode)
                            <option value="{{ $mode->value }}" @selected(old('mode', 'mobile_money') === $mode->value)>{{ $mode->label() }}</option>
                        @endforeach
                    </x-champ-select>

                    <x-champ nom="date_paiement" label="Date" type="date" requis :valeur="today()->format('Y-m-d')" />
                    <x-champ nom="reference" label="Référence" placeholder="Ex. TRX-9041289" />
                </div>

                <p class="aide text-[12px] text-muted">Un paiement partiel est possible.</p>

                <div class="modale-pied">
                    <x-bouton type="button" variante="contour" data-modale-fermer>Annuler</x-bouton>
                    <x-bouton type="submit">Enregistrer le paiement</x-bouton>
                </div>
            </form>
        </x-modale>
    @endif
@endsection
