@php
    $factures = $dossier->factures->sortByDesc('date_facture');
    $total = $factures->sum('total');
    $paye = $factures->sum(fn ($facture) => $facture->paiements->sum('montant'));
    $solde = max(0, $total - $paye);
@endphp

<div class="grid gap-5">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-mini">
            <p class="etiquette">Total facturé</p>
            <p class="valeur">{{ \App\Support\Format::nombre($total) }}<small>FCFA</small></p>
        </div>
        <div class="stat-mini">
            <p class="etiquette">Encaissé</p>
            <p class="valeur" style="color: var(--c-vert)">{{ \App\Support\Format::nombre($paye) }}<small>FCFA</small></p>
        </div>
        <div class="stat-mini">
            <p class="etiquette">Solde dû</p>
            <p class="valeur" style="color: {{ $solde > 0 ? 'var(--c-rose)' : 'var(--c-ink)' }}">
                {{ \App\Support\Format::nombre($solde) }}<small>FCFA</small>
            </p>
        </div>
    </div>

    <x-carte etiquette="Facturation" titre="Factures du dossier">
        @if ($factures->isNotEmpty())
            <div class="tableau-scroll">
<table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Numéro</th>
                        <th scope="col">Date</th>
                        <th scope="col">Échéance</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="cellule-droite">Total TTC</th>
                        <th scope="col" class="cellule-droite">Reste à payer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($factures as $facture)
                        <tr>
                            <td class="reference cellule-compacte">{{ $facture->numero }}</td>
                            <td class="cellule-compacte">{{ $facture->date_facture?->translatedFormat('j M Y') }}</td>
                            <td class="cellule-compacte">{{ $facture->date_echeance?->translatedFormat('j M Y') }}</td>
                            <td>
                                <x-badge :variante="match ($facture->statut->value) {
                                    'payee' => 'vert',
                                    'partielle' => 'jaune',
                                    default => 'rose',
                                }">{{ $facture->statut->label() }}</x-badge>
                            </td>
                            <td class="cellule-droite">{{ \App\Support\Format::nombre($facture->total) }}</td>
                            <td class="cellule-droite">{{ \App\Support\Format::nombre($facture->solde) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>
        @else
            <x-etat-vide icone="facturation" titre="Aucune facture" texte="Ce dossier n'a pas encore été facturé. La facturation sera disponible depuis le module Facturation." />
        @endif
    </x-carte>

    @if ($factures->isNotEmpty())
        <x-carte etiquette="Détail" titre="Lignes de la dernière facture">
            @php $derniere = $factures->first(); @endphp

            <div class="tableau-scroll">
<table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Désignation</th>
                        <th scope="col" class="cellule-droite">Qté</th>
                        <th scope="col" class="cellule-droite">P.U.</th>
                        <th scope="col" class="cellule-droite">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($derniere->lignes as $ligne)
                        <tr>
                            <td>{{ $ligne->designation }}</td>
                            <td class="cellule-droite">{{ $ligne->quantite }}</td>
                            <td class="cellule-droite">{{ \App\Support\Format::nombre($ligne->prix_unitaire) }}</td>
                            <td class="cellule-droite">{{ \App\Support\Format::nombre($ligne->montant) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
</div>

            <div class="mt-4 border-t pt-3 text-[13px]" style="border-color: var(--c-line-soft)">
                <div class="ligne-donnee"><span class="label">Sous-total</span><span class="valeur">{{ \App\Support\Format::nombre($derniere->sous_total) }} FCFA</span></div>
                <div class="ligne-donnee"><span class="label">TVA ({{ rtrim(rtrim(number_format($derniere->tva_taux, 2, ',', ''), '0'), ',') }} %)</span><span class="valeur">{{ \App\Support\Format::nombre($derniere->tva_montant) }} FCFA</span></div>
                <div class="ligne-donnee"><span class="label">Total TTC</span><span class="valeur">{{ \App\Support\Format::nombre($derniere->total) }} FCFA</span></div>
            </div>
        </x-carte>
    @endif
</div>
