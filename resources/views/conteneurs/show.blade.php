@extends('layouts.app')

@section('titre-onglet', 'Conteneur '.$conteneur->numero_formate)

@section('titre')
    Conteneur <span style="color: var(--c-brand)">{{ $conteneur->numero_formate }}</span>
@endsection

@section('sous-titre')
    {{ $conteneur->dossier?->reference }} · {{ $conteneur->dossier?->client?->nom }}
@endsection

@section('fil')
    <a href="{{ route('conteneurs.index') }}">Conteneurs</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">{{ $conteneur->numero_formate }}</span>
@endsection

@section('actions')
    <x-bouton href="{{ route('conteneurs.index') }}" variante="contour">Retour à la liste</x-bouton>
@endsection

@section('contenu')
    @php
        $jours = $conteneur->jours_restants;
        $echeance = $conteneur->echeance;

        // Code et couleur du bandeau de franchise.
        [$varianteBandeau, $codeBandeau, $titreBandeau] = match (true) {
            $conteneur->statut->value !== 'au_port' => ['', '—', 'Franchise clôturée'],
            $jours === null => ['', '—', 'Franchise non calculée'],
            $jours <= 0 => ['rose', 'J0', 'Franchise dépassée'],
            $jours === 1 => ['ambre', 'J-1', 'Franchise dans 1 jour'],
            $jours === 2 => ['jaune', 'J-2', 'Franchise dans 2 jours'],
            default => ['', $jours.' j', 'Franchise dans '.$jours.' jours'],
        };

        $libellesType = [
            '20DV' => 'Dry Van 20 pieds',
            '40DV' => 'Dry Van 40 pieds',
            '20HC' => 'High Cube 20 pieds',
            '40HC' => 'High Cube 40 pieds',
            '20RF' => 'Reefer 20 pieds',
            '40RF' => 'Reefer 40 pieds',
        ];

        $caracteristiques = collect([
            $libellesType[$conteneur->type] ?? $conteneur->type,
            $conteneur->poids_kg ? \App\Support\Format::nombre($conteneur->poids_kg).' kg' : null,
            $conteneur->numero_plomb ? 'Plomb '.$conteneur->numero_plomb : null,
        ])->filter()->implode(' · ');

        $dureeFranchise = ($conteneur->date_arrivee && $conteneur->fin_franchise)
            ? (int) $conteneur->date_arrivee->diffInDays($conteneur->fin_franchise)
            : null;

        $peutModifier = in_array(auth()->user()->role->value, ['administrateur', 'transitaire'], true);
    @endphp

    <div class="fiche-conteneur grid gap-5 lg:grid-cols-[minmax(0,1.55fr)_minmax(0,1fr)]">
        <x-carte etiquette="Données techniques" titre="Informations">
            <div @class(['bandeau-franchise', $varianteBandeau])>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="pastille-sombre">Type {{ $conteneur->type }}</span>
                        <x-badge :variante="match ($conteneur->statut->value) {
                            'au_port' => 'bleu',
                            'restitue' => 'vert',
                            default => null,
                        }">{{ $conteneur->statut->label() }}</x-badge>
                    </div>
                    <p class="bandeau-titre">{{ $titreBandeau }}</p>
                </div>

                <span class="bandeau-code">{{ $codeBandeau }}</span>
            </div>

            <div class="mt-4">
                <div class="ligne-info">
                    <span class="cle">Dossier</span>
                    <span class="valeur">
                        @if ($conteneur->dossier)
                            <a href="{{ route('dossiers.show', $conteneur->dossier) }}" style="color: var(--c-brand);font-weight:600">
                                {{ $conteneur->dossier->reference }}
                            </a>
                        @else
                            —
                        @endif
                    </span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Client</span>
                    <span class="valeur">{{ $conteneur->dossier?->client?->nom ?? '—' }}</span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Caractéristiques</span>
                    <span class="valeur">{{ $caracteristiques }}</span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Ligne maritime</span>
                    <span class="valeur">{{ $conteneur->ligne_maritime ?? '—' }}</span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Franchise applicable</span>
                    <span class="valeur">
                        {{ $dureeFranchise !== null ? $dureeFranchise.' jours calendaires' : '—' }}
                    </span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Arrivée au quai</span>
                    <span class="valeur">{{ $conteneur->date_arrivee?->translatedFormat('j M Y') ?? '—' }}</span>
                </div>

                <div class="ligne-info">
                    <span class="cle">Date limite franchise</span>
                    <span class="valeur" @if ($conteneur->estEnSurestarie()) style="color: var(--c-rose);font-weight:600" @endif>
                        {{ $conteneur->fin_franchise?->translatedFormat('j M Y') ?? '—' }}
                        @if ($conteneur->estEnSurestarie())
                            (Échue)
                        @endif
                    </span>
                </div>

                @if ($conteneur->date_sortie || $conteneur->date_restitution)
                    <div class="ligne-info">
                        <span class="cle">Sortie du port</span>
                        <span class="valeur">{{ $conteneur->date_sortie?->translatedFormat('j M Y') ?? '—' }}</span>
                    </div>

                    <div class="ligne-info">
                        <span class="cle">Restitution</span>
                        <span class="valeur">{{ $conteneur->date_restitution?->translatedFormat('j M Y') ?? '—' }}</span>
                    </div>
                @endif
            </div>

            <p class="mt-4 text-[12px] text-muted" style="font-style:italic">
                Franchise : valeur de départ, à confirmer avec un transitaire.
            </p>

            @if ($peutModifier)
                <div class="mt-5 border-t pt-5" style="border-color: var(--c-line-soft)">
                    <p class="etiquette mb-3">Mettre à jour le statut</p>

                    <form method="POST" action="{{ route('conteneurs.statut', $conteneur) }}" class="flex flex-wrap items-center gap-2">
                        @csrf

                        <label class="sr-only" for="statut">Nouveau statut</label>
                        <select id="statut" name="statut" style="height:40px;min-width:170px;padding:0 12px;border:1px solid var(--c-champ-bord);border-radius:6px;background:var(--c-white);color:var(--c-ink);font-size:13.5px">
                            @foreach (\App\Enums\StatutConteneur::cases() as $option)
                                <option value="{{ $option->value }}" @selected($conteneur->statut === $option)>{{ $option->label() }}</option>
                            @endforeach
                        </select>

                        <x-bouton type="submit">Enregistrer le statut</x-bouton>
                        <x-bouton href="{{ route('conteneurs.show', $conteneur) }}" variante="contour">Annuler</x-bouton>
                    </form>

                    @error('statut')
                        <p class="mt-2 text-[12.5px]" style="color: var(--c-rose)">{{ $message }}</p>
                    @enderror
                </div>
            @endif
        </x-carte>

        <x-carte etiquette="Traçabilité portuaire" titre="Historique des événements">
            @forelse ($conteneur->evenements as $evenement)
                <div @class([
                    'chronologie-item',
                    'actif' => false,
                    'rose' => $evenement->type === 'franchise',
                    'bleu' => $evenement->type === 'arrivee',
                    'ambre' => $evenement->type === 'sortie',
                ])>
                    <p class="titre-chrono">{{ $evenement->libelle }}</p>
                    <p class="meta-chrono">
                        {{ $evenement->date_evenement?->translatedFormat('j M Y') }}
                        @if ($evenement->auteur)
                            · {{ \App\Support\Format::nomCourt($evenement->auteur->nom) }}
                        @endif
                    </p>
                    @if ($evenement->description)
                        <p class="corps-chrono">{{ $evenement->description }}</p>
                    @endif
                </div>
            @empty
                <x-etat-vide
                    icone="conteneurs"
                    titre="Aucun événement"
                    texte="Les événements du conteneur apparaîtront ici."
                />
            @endforelse

            @if ($conteneur->evenements->isNotEmpty())
                <p class="mt-5 text-[12px] text-muted">
                    @if ($echeance)
                        Échéance {{ $echeance->value }} : une alerte est envoyée au client et au déclarant.
                    @else
                        Aucune alerte de franchise en cours pour ce conteneur.
                    @endif
                </p>
            @endif
        </x-carte>
    </div>
@endsection
