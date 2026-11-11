@extends('layouts.app')

@section('titre-onglet', 'Nouvelle facture')

@section('titre', 'Nouvelle facture')

@section('sous-titre', 'Le numéro est généré, les montants se calculent automatiquement.')

@section('fil')
    <a href="{{ route('facturation.index') }}">Facturation</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">Nouvelle facture</span>
@endsection

@section('contenu')
    <form method="POST" action="{{ route('facturation.store') }}" data-facture>
        @csrf

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
            <div class="grid gap-5 content-start">
                <x-carte titre="Informations de la facture">
                    <div class="groupe-champs">
                        <x-champ-select nom="client_id" label="Client" requis placeholder="Choisir un client">
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) old('client_id', $clientSelectionne?->id) === (string) $client->id)>
                                    {{ $client->nom }}
                                </option>
                            @endforeach
                        </x-champ-select>

                        <x-champ-select nom="dossier_id" label="Dossier rattaché" requis placeholder="Choisir un dossier">
                            @foreach ($dossiers as $dossier)
                                <option value="{{ $dossier->id }}" data-client="{{ $dossier->client_id }}"
                                        @selected((string) old('dossier_id', $dossierSelectionne) === (string) $dossier->id)>
                                    {{ $dossier->reference }} ({{ $dossier->client?->nom }})
                                </option>
                            @endforeach
                        </x-champ-select>

                        <div class="champ">
                            <span class="champ-label">Numéro de facture</span>
                            <input type="text" value="{{ $numero }} (généré)" disabled>
                        </div>

                        <x-champ-select nom="tva_taux" label="Taxe applicable">
                            @foreach ([18 => 'TVA normale (18 %)', 0 => 'Exonérée (0 %)'] as $taux => $libelle)
                                <option value="{{ $taux }}" @selected((string) old('tva_taux', (int) $tvaTaux) === (string) $taux)>{{ $libelle }}</option>
                            @endforeach
                        </x-champ-select>

                        <x-champ nom="date_facture" label="Date d'émission" type="date" requis :valeur="today()->format('Y-m-d')" />

                        <x-champ nom="date_echeance" label="Date d'échéance" type="date" requis :valeur="today()->addDays(15)->format('Y-m-d')" />
                    </div>

                    <x-champ-zone nom="notes" label="Notes" placeholder="Mentions particulières, conditions de règlement…" />
                </x-carte>

                <x-carte titre="Prestations et débours" sous-titre="Montants en FCFA">
                    <div class="tableau-scroll">
                        <table class="tableau" data-tableau-lignes>
                            <thead>
                                <tr>
                                    <th scope="col" style="min-width:200px">Désignation</th>
                                    <th scope="col" style="width:90px">Qté</th>
                                    <th scope="col" style="width:150px">Prix unitaire</th>
                                    <th scope="col" style="width:130px">Montant</th>
                                    <th scope="col" style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody data-lignes>
                                @for ($i = 0; $i < 2; $i++)
                                    <tr data-ligne>
                                        <td>
                                            <input type="text" name="lignes[{{ $i }}][designation]" value="{{ old('lignes.'.$i.'.designation') }}"
                                                   placeholder="Ex. Honoraires de transit" aria-label="Désignation"
                                                   style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                                        </td>
                                        <td>
                                            <input type="number" name="lignes[{{ $i }}][quantite]" value="{{ old('lignes.'.$i.'.quantite', 1) }}"
                                                   min="0.01" step="1" aria-label="Quantité"
                                                   style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                                        </td>
                                        <td>
                                            <input type="number" name="lignes[{{ $i }}][prix_unitaire]" value="{{ old('lignes.'.$i.'.prix_unitaire') }}"
                                                   min="0" step="1" placeholder="0" aria-label="Prix unitaire"
                                                   style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                                        </td>
                                        <td><span data-montant style="font-weight:600">0</span></td>
                                        <td>
                                            <button type="button" data-supprimer-ligne aria-label="Supprimer la ligne" style="color: var(--c-muted)">
                                                <x-icone nom="fermer" :taille="14" />
                                            </button>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <button type="button" data-ajouter-ligne class="lien-action mt-3" style="color: var(--c-teal)">
                        + Ajouter une ligne
                    </button>

                    <template data-gabarit-ligne>
                        <tr data-ligne>
                            <td>
                                <input type="text" name="lignes[0][designation]" placeholder="Ex. Transport et livraison" aria-label="Désignation"
                                       style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                            </td>
                            <td>
                                <input type="number" name="lignes[0][quantite]" value="1" min="0.01" step="1" aria-label="Quantité"
                                       style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                            </td>
                            <td>
                                <input type="number" name="lignes[0][prix_unitaire]" min="0" step="1" placeholder="0" aria-label="Prix unitaire"
                                       style="width:100%;height:36px;padding:0 10px;border:1px solid var(--c-line);border-radius:8px;background:var(--c-white);color:var(--c-ink)">
                            </td>
                            <td><span data-montant style="font-weight:600">0</span></td>
                            <td>
                                <button type="button" data-supprimer-ligne aria-label="Supprimer la ligne" style="color: var(--c-muted)">
                                    <x-icone nom="fermer" :taille="14" />
                                </button>
                            </td>
                        </tr>
                    </template>

                    @error('lignes')
                        <p class="mt-2 text-[12.5px]" style="color: var(--c-rose)">{{ $message }}</p>
                    @enderror
                </x-carte>
            </div>

            <div class="grid gap-4 content-start">
                <x-carte titre="Totaux">
                    <div class="ligne-info">
                        <span class="cle">Sous-total HT</span>
                        <span class="valeur"><span data-sous-total>0</span> <span class="text-muted">FCFA</span></span>
                    </div>

                    <div class="ligne-info">
                        <span class="cle">Remise</span>
                        <span class="valeur" style="display:inline-flex;align-items:center;gap:6px">
                            <input type="number" name="remise" value="{{ old('remise', 0) }}" min="0" step="1" aria-label="Remise"
                                   style="width:96px;height:32px;padding:0 8px;border:1px solid var(--c-line);border-radius:6px;background:var(--c-white);text-align:right;color:var(--c-ink)">
                            <span class="text-muted">FCFA</span>
                        </span>
                    </div>

                    <div class="ligne-info">
                        <span class="cle">TVA</span>
                        <span class="valeur"><span data-tva>0</span> <span class="text-muted">FCFA</span></span>
                    </div>

                    <div class="ligne-info" style="margin-top:8px;padding:12px 14px;border:0;border-radius:10px;background: var(--c-surface-2)">
                        <span class="cle" style="color:var(--c-ink);font-weight:600">Total TTC</span>
                        <span class="valeur" style="font-size:19px;color:var(--c-brand)">
                            <span data-total>0</span> <span class="text-muted" style="font-size:12px">FCFA</span>
                        </span>
                    </div>

                    <p class="mt-3 text-[12px] text-muted">
                        La facture sera enregistrée dans les créances clients et rattachée à l'historique du dossier sélectionné.
                    </p>
                </x-carte>

                <div class="grid gap-2">
                    <x-bouton type="submit">Émettre la facture</x-bouton>
                    <x-bouton href="{{ route('facturation.index') }}" variante="contour">Annuler</x-bouton>
                </div>
            </div>
        </div>
    </form>
@endsection
