@extends('layouts.app')

@section('titre-onglet', 'Facture '.$facture->numero)

@section('titre')
    Facture <span style="color: var(--c-brand)">{{ $facture->numero }}</span>
@endsection

@section('sous-titre')
    {{ $facture->client?->nom }}
    @if ($facture->dossier)
        · {{ $facture->dossier->reference }}
    @endif
@endsection

@section('fil')
    <a href="{{ route('facturation.index') }}">Facturation</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">{{ $facture->numero }}</span>
@endsection

@section('actions')
    <div class="flex flex-wrap items-center gap-2">
        <x-bouton href="{{ route('facturation.imprimable', $facture) }}" variante="contour" icone="telecharger">
            Télécharger le PDF
        </x-bouton>

        @if ($facture->solde > 0)
            <x-bouton type="button" data-modale-ouvrir="#modale-paiement">Enregistrer un paiement</x-bouton>
        @endif
    </div>
@endsection

@section('contenu')
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <x-carte class="fiche-conteneur" etiquette="Détail" titre="Prestations et débours">
            @include('facturation.partiels.detail', ['facture' => $facture])
        </x-carte>

        <div class="grid gap-4 content-start">
            <x-carte etiquette="Encaissements" titre="Paiements enregistrés">
                @forelse ($facture->paiements as $paiement)
                    <div class="ligne-info">
                        <div class="min-w-0">
                            <p class="titre">{{ $paiement->mode->label() }}</p>
                            <p class="detail">
                                {{ $paiement->date_paiement?->translatedFormat('j M Y') }}
                                @if ($paiement->reference)
                                    · {{ $paiement->reference }}
                                @endif
                                @if ($paiement->auteur)
                                    · {{ \App\Support\Format::nomCourt($paiement->auteur->nom) }}
                                @endif
                            </p>
                        </div>
                        <span class="valeur" style="color: var(--c-vert);font-weight:600">
                            {{ \App\Support\Format::nombre($paiement->montant) }} FCFA
                        </span>
                    </div>
                @empty
                    <x-etat-vide icone="paiement" titre="Aucun paiement" texte="Les règlements enregistrés apparaîtront ici." />
                @endforelse

                @if ($facture->paiements->isNotEmpty())
                    <div class="mt-3 flex items-center justify-between border-t pt-3" style="border-color: var(--c-line-soft)">
                        <span class="text-[12.5px] text-muted">Total encaissé</span>
                        <span class="font-semibold">{{ \App\Support\Format::nombre($facture->montant_paye) }} FCFA</span>
                    </div>
                @endif
            </x-carte>

            <x-carte etiquette="Informations" titre="Suivi de la facture">
                <div class="ligne-info">
                    <span class="cle">Émise le</span>
                    <span class="valeur">{{ $facture->date_facture?->translatedFormat('j M Y') ?? '—' }}</span>
                </div>
                <div class="ligne-info">
                    <span class="cle">Échéance</span>
                    <span class="valeur" @if ($facture->estEnRetard()) style="color: var(--c-rose)" @endif>
                        {{ $facture->date_echeance?->translatedFormat('j M Y') ?? '—' }}
                    </span>
                </div>
                <div class="ligne-info">
                    <span class="cle">Émise par</span>
                    <span class="valeur">{{ $facture->emetteur?->nom ?? '—' }}</span>
                </div>
                <div class="ligne-info">
                    <span class="cle">Statut</span>
                    <span class="valeur">{{ $facture->statut->label() }}</span>
                </div>

                @if ($facture->notes)
                    <p class="mt-3 text-[12.5px] text-muted">{{ $facture->notes }}</p>
                @endif
            </x-carte>

            <x-bouton href="{{ route('facturation.index') }}" variante="contour">Retour à la facturation</x-bouton>
        </div>
    </div>

    @if ($facture->solde > 0)
        <x-modale id="modale-paiement" titre="Enregistrer un paiement" :sous-titre="'Facture '.$facture->numero.' · solde restant '.\App\Support\Format::nombre($facture->solde).' FCFA'">
            <form method="POST" action="{{ route('facturation.paiement', $facture) }}">
                @csrf

                <div class="groupe-champs">
                    <x-champ nom="montant" label="Montant (FCFA)" type="number" requis :valeur="$facture->solde" min="1" step="1" />
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
