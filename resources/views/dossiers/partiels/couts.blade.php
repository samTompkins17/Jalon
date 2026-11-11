@php
    $peutSaisir = auth()->user()->permission('saisir_couts') || auth()->user()->estAdministrateur();
    $totalCouts = $dossier->couts->sum('montant');
    $facture = $dossier->factures->sum('total');
@endphp

<div class="grid gap-5">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="stat-mini">
            <p class="etiquette">Total des coûts</p>
            <p class="valeur">{{ \App\Support\Format::nombre($totalCouts) }}<small>FCFA</small></p>
        </div>
        <div class="stat-mini">
            <p class="etiquette">Total facturé</p>
            <p class="valeur">{{ \App\Support\Format::nombre($facture) }}<small>FCFA</small></p>
        </div>
        <div class="stat-mini">
            <p class="etiquette">Marge réelle</p>
            <p class="valeur" style="color: {{ $dossier->marge_reelle < 0 ? 'var(--c-rose)' : 'var(--c-ink)' }}">
                {{ \App\Support\Format::nombre($dossier->marge_reelle) }}<small>FCFA</small>
            </p>
        </div>
    </div>

    <x-carte etiquette="Débours" titre="Coûts saisis">
        <div class="tableau-scroll">
<table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Libellé</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col">Fournisseur</th>
                    <th scope="col">Date</th>
                    <th scope="col" class="cellule-droite">Montant (FCFA)</th>
                    @if ($peutSaisir)
                        <th scope="col" class="cellule-droite">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($dossier->couts as $cout)
                    <tr>
                        <td class="cellule-client">{{ $cout->libelle }}</td>
                        <td>{{ $cout->categorie_label }}</td>
                        <td>{{ $cout->fournisseur ?: '—' }}</td>
                        <td class="cellule-compacte">{{ $cout->date?->translatedFormat('j M Y') }}</td>
                        <td class="cellule-droite">{{ \App\Support\Format::nombre($cout->montant) }}</td>
                        @if ($peutSaisir)
                            <td class="cellule-droite">
                                <form method="POST" action="{{ route('dossiers.couts.destroy', [$dossier, $cout]) }}"
                                      data-confirmer="Supprimer ce coût ?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="actions-ligne" style="color: var(--c-rose)">Supprimer</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $peutSaisir ? 6 : 5 }}">
                            <x-etat-vide icone="paiement" titre="Aucun coût saisi" texte="Enregistrez les débours portuaires et les frais réels du dossier." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
</div>
    </x-carte>

    @if ($peutSaisir)
        <x-carte etiquette="Saisie" titre="Ajouter un coût">
            <form method="POST" action="{{ route('dossiers.couts.store', $dossier) }}">
                @csrf

                <div class="groupe-champs">
                    <x-champ nom="libelle" label="Libellé" placeholder="Ex. Débours portuaires" requis />
                    <x-champ-select nom="categorie" label="Catégorie" requis>
                        @foreach (['transit' => 'Transit', 'douane' => 'Douane', 'manutention' => 'Manutention', 'transport' => 'Transport', 'autre' => 'Divers'] as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(old('categorie') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </x-champ-select>
                    <x-champ nom="montant" label="Montant (FCFA)" type="number" placeholder="Ex. 120000" requis />
                    <x-champ nom="date" label="Date" type="date" :valeur="today()->format('Y-m-d')" requis />
                    <x-champ nom="fournisseur" label="Fournisseur (facultatif)" placeholder="Ex. SOTRAMAR" />
                </div>

                <div class="flex justify-end">
                    <x-bouton type="submit" icone="plus">Enregistrer le coût</x-bouton>
                </div>
            </form>
        </x-carte>
    @endif
</div>
