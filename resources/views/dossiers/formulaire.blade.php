@extends('layouts.app')

@php
    $modification = $dossier !== null;
@endphp

@section('titre', $modification ? 'Modifier le dossier '.$dossier->reference : 'Nouveau dossier')

@section('sous-titre', $modification
    ? 'La référence et le code de suivi restent inchangés'
    : 'La référence et le code de suivi sont générés à la création')

@section('fil')
    <a href="{{ route('dossiers.index') }}">Dossiers</a>
    <span class="separateur-fil">/</span>
    @if ($modification)
        <a href="{{ route('dossiers.show', $dossier) }}">{{ $dossier->reference }}</a>
        <span class="separateur-fil">/</span>
        <span>Modifier</span>
    @else
        <span>Nouveau dossier</span>
    @endif
@endsection

@section('actions')
    <x-bouton href="{{ $modification ? route('dossiers.show', $dossier) : route('dossiers.index') }}" variante="contour">
        Annuler
    </x-bouton>
@endsection

@section('contenu')
    <form method="POST"
          action="{{ $modification ? route('dossiers.update', $dossier) : route('dossiers.store') }}"
          class="grid gap-5 lg:grid-cols-[minmax(0,1.75fr)_minmax(0,1fr)]">
        @csrf
        @if ($modification)
            @method('PUT')
        @endif

        <div class="carte">
            <div class="carte-corps">
                <div class="groupe-champs">
                    <x-champ-select nom="client_id" label="Client" requis placeholder="Choisir un client">
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) old('client_id', $dossier?->client_id ?? $clientSelectionne) === (string) $client->id)>{{ $client->nom }}</option>
                        @endforeach
                    </x-champ-select>

                    <x-champ-select nom="regime_id" label="Régime douanier" requis placeholder="Choisir un régime" id="regime_id">
                        @foreach ($regimes as $regime)
                            <option value="{{ $regime->id }}" @selected((string) old('regime_id', $dossier?->regime_id) === (string) $regime->id)>{{ $regime->nom }} ({{ $regime->code }})</option>
                        @endforeach
                    </x-champ-select>

                    <div class="champ">
                        <span class="champ-label">Référence dossier</span>
                        <input type="text" value="{{ $reference }} (générée)" disabled
                               style="color: var(--c-brand); font-weight: 600; background: var(--c-surface-2);">
                    </div>

                    <x-champ nom="ligne_maritime" label="Ligne maritime" :valeur="$dossier?->ligne_maritime" placeholder="Ex. MSC, Maersk, CMA CGM" requis />

                    <x-champ nom="numero_bl" label="N° de connaissance (B/L)" :valeur="$dossier?->numero_bl" placeholder="Ex. MEDULOM920145" />

                    <x-champ nom="nature_marchandise" label="Nature de la marchandise" :valeur="$dossier?->nature_marchandise" placeholder="Ex. Produits alimentaires, quincaillerie" requis />

                    <x-champ nom="pays_origine" label="Pays d'origine" :valeur="$dossier?->pays_origine" placeholder="Ex. Chine, France, Turquie" />

                    <x-champ nom="navire" label="Navire" :valeur="$dossier?->navire" placeholder="Ex. MSC Katrina" />

                    <div class="md:col-span-2">
                        <x-champ
                            nom="date_arrivee_prevue"
                            label="Arrivée prévue"
                            type="date"
                            :valeur="$dossier?->date_arrivee_prevue?->format('Y-m-d')"
                        />
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3 border-t pt-5" style="border-color: var(--c-line-soft)">
                    <x-bouton type="submit" name="brouillon" value="0">
                        {{ $modification ? 'Enregistrer les modifications' : 'Créer le dossier' }}
                    </x-bouton>

                    <x-bouton type="submit" name="brouillon" value="1" variante="contour">
                        Enregistrer en brouillon
                    </x-bouton>
                </div>
            </div>
        </div>

        <div class="grid gap-5 content-start">
            <x-carte etiquette="Pièces justificatives" titre="Documents attendus" sous-titre="Selon le régime choisi. Liste à confirmer avec un transitaire.">
                <div id="documents-attendus" class="grid gap-2"></div>

                <div class="bandeau bandeau--info mt-4">
                    <x-icone nom="info" :taille="16" />
                    <span>
                        Les documents complémentaires (ECTN/BESC, certificat d'origine) pourront être ajoutés
                        au jalon 2, depuis la fiche du dossier.
                    </span>
                </div>
            </x-carte>

            <x-carte etiquette="Traçabilité" titre="Ce qui est créé automatiquement">
                <ul class="grid gap-3 text-[13px]" style="color: var(--c-ink-2)">
                    <li class="flex items-start gap-2">
                        <x-icone nom="check" :taille="16" class="mt-0.5" style="color: var(--c-brand)" />
                        <span>Référence <strong>{{ $reference }}</strong> attribuée à l'agence.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icone nom="check" :taille="16" class="mt-0.5" style="color: var(--c-brand)" />
                        <span>Code de suivi public <strong>{{ $codeSuivi }}</strong> pour le portail client.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icone nom="check" :taille="16" class="mt-0.5" style="color: var(--c-brand)" />
                        <span>Entrée au jalon 1 et première ligne d'historique.</span>
                    </li>
                </ul>
            </x-carte>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const documents = @json($documentsParRegime);
            const regime = document.getElementById('regime_id');
            const zone = document.getElementById('documents-attendus');

            function afficher() {
                const liste = documents[regime.value] || [];
                zone.innerHTML = '';

                if (liste.length === 0) {
                    zone.innerHTML = '<p class="text-[13px] text-muted">Aucune pièce obligatoire pour ce régime.</p>';
                    return;
                }

                liste.forEach((libelle) => {
                    const ligne = document.createElement('div');
                    ligne.className = 'ligne-info';
                    ligne.innerHTML = `
                        <div class="flex items-center gap-3">
                            <span style="display:grid;place-items:center;width:22px;height:22px;border-radius:6px;background:var(--c-brand);color:#fff;flex:none">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="titre" style="font-size:13px">${libelle}</span>
                        </div>
                        <span class="badge badge--petit">À fournir</span>
                    `;
                    zone.appendChild(ligne);
                });
            }

            if (regime) {
                regime.addEventListener('change', afficher);
                afficher();
            }
        })();
    </script>
@endpush
