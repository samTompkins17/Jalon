@php
    $peutModifier = auth()->user()->permission('modifier_dossiers') || auth()->user()->estAdministrateur();
@endphp

<div class="grid gap-5">
    <x-carte etiquette="Matériel roulant" titre="Conteneurs du dossier">
        <x-slot:actions>
            @if ($peutModifier)
                <x-bouton type="button" taille="petit" variante="contour" data-modale-ouvrir="#modale-conteneur" icone="plus">
                    Ajouter un conteneur
                </x-bouton>
            @endif
        </x-slot:actions>

        @forelse ($dossier->conteneurs as $conteneur)
            @php
                $jours = $conteneur->jours_restants;
            @endphp

            <div class="carte mb-4" style="background: var(--c-white)">
                <div class="carte-corps" style="padding: 16px 18px">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-[15px] font-semibold">{{ $conteneur->numero_formate }}</p>
                            <p class="text-[12.5px] text-muted">
                                {{ $conteneur->type }} · {{ $conteneur->taille }} · {{ $conteneur->ligne_maritime }}
                                @if ($conteneur->numero_plomb)
                                    · plomb {{ $conteneur->numero_plomb }}
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-badge :variante="match ($conteneur->statut->value) {
                                'au_port' => 'bleu',
                                'restitue' => 'vert',
                                default => null,
                            }">{{ $conteneur->statut->label() }}</x-badge>

                            @if ($conteneur->statut->value === 'au_port' && $jours !== null)
                                @if ($jours <= 0)
                                    <x-badge variante="rose">Franchise dépassée</x-badge>
                                @elseif ($jours <= 2)
                                    <x-badge variante="ambre">J-{{ $jours }}</x-badge>
                                @else
                                    <x-badge variante="jaune">{{ $jours }} jours restants</x-badge>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <div>
                            <p class="etiquette">Arrivée</p>
                            <p class="text-[13px]">{{ $conteneur->date_arrivee?->translatedFormat('j M Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="etiquette">Fin de franchise</p>
                            <p class="text-[13px]">{{ $conteneur->fin_franchise?->translatedFormat('j M Y') ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="etiquette">Sortie / restitution</p>
                            <p class="text-[13px]">
                                {{ $conteneur->date_sortie?->translatedFormat('j M Y')
                                    ?? $conteneur->date_restitution?->translatedFormat('j M Y')
                                    ?? '—' }}
                            </p>
                        </div>
                    </div>

                    @if ($conteneur->evenements->isNotEmpty())
                        <div class="mt-4 border-t pt-4" style="border-color: var(--c-line-soft)">
                            <p class="etiquette mb-3">Événements</p>

                            <div class="chronologie">
                                @foreach ($conteneur->evenements as $evenement)
                                    <div @class(['chronologie-item', 'actif' => $loop->first])>
                                        <p class="titre-chrono">{{ $evenement->libelle }}</p>
                                        <p class="meta-chrono">{{ $evenement->date_evenement?->translatedFormat('j M Y') }}</p>
                                        @if ($evenement->description)
                                            <p class="corps-chrono">{{ $evenement->description }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <x-etat-vide icone="conteneurs" titre="Aucun conteneur" texte="Ajoutez les conteneurs rattachés à ce dossier pour suivre leur franchise." />
        @endforelse
    </x-carte>

    <p class="text-[12.5px] text-muted">
        La franchise est calculée automatiquement selon le régime douanier et la ligne maritime.
        Les alertes J-2, J-1 et J0 sont générées chaque matin.
    </p>
</div>

@if ($peutModifier)
    <x-modale id="modale-conteneur" titre="Ajouter un conteneur" :sous-titre="'Dossier '.$dossier->reference">
        <form method="POST" action="{{ route('dossiers.conteneurs.store', $dossier) }}">
            @csrf

            <div class="groupe-champs">
                <x-champ nom="numero" label="Numéro du conteneur" placeholder="Ex. MSCU4521870" requis />
                <x-champ nom="numero_plomb" label="Numéro de plomb" placeholder="Ex. PLB123456" />
                <x-champ-select nom="type" label="Type" requis>
                    @foreach (['20DV', '40DV', '20HC', '40HC', '20RF', '40RF'] as $type)
                        <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                    @endforeach
                </x-champ-select>
                <x-champ nom="date_arrivee" label="Date d'arrivée" type="date" :valeur="today()->format('Y-m-d')" />
            </div>

            <p class="aide text-[12px] text-muted">
                La fin de franchise sera calculée pour la ligne {{ $dossier->ligne_maritime }}.
            </p>

            <div class="modale-pied">
                <x-bouton type="button" variante="contour" data-modale-fermer>Annuler</x-bouton>
                <x-bouton type="submit">Enregistrer le conteneur</x-bouton>
            </div>
        </form>
    </x-modale>
@endif
