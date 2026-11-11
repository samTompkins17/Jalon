@php
    $fournis = $dossier->documents->groupBy('type');
    $peutAjouter = auth()->user()->permission('ajouter_documents') || auth()->user()->estAdministrateur();
@endphp

<div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
    <div class="grid gap-5 content-start">
        <x-carte etiquette="Pièces du dossier" titre="Documents téléversés">
            <x-slot:actions>
                <span class="text-[12.5px] text-muted">{{ $dossier->documents->count() }} fichier{{ $dossier->documents->count() > 1 ? 's' : '' }}</span>
            </x-slot:actions>

            @forelse ($dossier->documents as $document)
                <div class="ligne-document">
                    <div class="flex items-start gap-3 min-w-0">
                        <span class="avatar avatar--neutre" style="width:34px;height:34px;border-radius:10px;font-size:10px">
                            {{ strtoupper(pathinfo($document->nom_original, PATHINFO_EXTENSION)) }}
                        </span>

                        <div class="min-w-0">
                            <p class="nom-fichier">{{ $document->type }}</p>
                            <p class="meta-fichier">
                                {{ $document->nom_original }} · {{ number_format($document->taille / 1024, 0, ',', ' ') }} Ko
                                @if ($document->auteur)
                                    · ajouté par {{ \App\Support\Format::nomCourt($document->auteur->nom) }}
                                @endif
                                · {{ $document->created_at?->translatedFormat('j M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-none items-center gap-2">
                        @if ($document->valide)
                            <x-badge variante="vert">Validé</x-badge>
                        @else
                            <x-badge variante="jaune">À valider</x-badge>
                        @endif

                        <a href="{{ route('dossiers.documents.telecharger', [$dossier, $document]) }}" class="btn btn-petit btn-fantome" title="Télécharger">
                            <x-icone nom="telecharger" :taille="15" />
                        </a>

                        @if ($peutAjouter)
                            <form method="POST" action="{{ route('dossiers.documents.valider', [$dossier, $document]) }}">
                                @csrf
                                <button type="submit" class="btn btn-petit btn-contour">
                                    {{ $document->valide ? 'Retirer' : 'Valider' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('dossiers.documents.destroy', [$dossier, $document]) }}"
                                  data-confirmer="Supprimer définitivement ce document ?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-petit btn-fantome" title="Supprimer" style="color: var(--c-rose)">
                                    <x-icone nom="corbeille" :taille="15" />
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <x-etat-vide icone="document" titre="Aucun document" texte="Téléversez les pièces justificatives du dossier pour avancer au jalon 2." />
            @endforelse
        </x-carte>

        @if ($peutAjouter)
            <x-carte etiquette="Téléversement" titre="Ajouter un document">
                <form method="POST" action="{{ route('dossiers.documents.store', $dossier) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="groupe-champs">
                        <x-champ-select nom="type" label="Type de pièce" requis placeholder="Choisir un type">
                            @foreach ($documentsRequis as $piece)
                                <option value="{{ $piece->libelle }}" @selected(old('type') === $piece->libelle)>{{ $piece->libelle }}</option>
                            @endforeach
                            <option value="Autre" @selected(old('type') === 'Autre')>Autre pièce</option>
                        </x-champ-select>

                        <div class="champ">
                            <span class="champ-label">Fichier (PDF, JPG ou PNG — 5 Mo max.)</span>
                            <input type="file" name="fichier" accept="application/pdf,image/jpeg,image/png" required>
                        </div>
                    </div>

                    <x-bascule
                        nom="visible_client"
                        intitule="Visible dans le portail client"
                        precision="Le client pourra télécharger ce document depuis son espace de suivi."
                        :actif="true"
                    />

                    <div class="mt-5 flex justify-end">
                        <x-bouton type="submit" icone="plus">Téléverser</x-bouton>
                    </div>
                </form>
            </x-carte>
        @endif
    </div>

    <x-carte etiquette="Contrôle" titre="Pièces exigées par le régime" :sous-titre="($dossier->regime?->nom ?? '').' ('.$dossier->regime?->code.')'" class="content-start">
        @forelse ($documentsRequis as $piece)
            @php
                $pieces = $fournis->get($piece->libelle, collect());
                $valide = $pieces->where('valide', true)->isNotEmpty();
            @endphp

            <div class="ligne-donnee">
                <span class="label" style="max-width: 62%">{{ $piece->libelle }}</span>
                <span class="valeur">
                    @if ($valide)
                        <x-badge variante="vert">Validé</x-badge>
                    @elseif ($pieces->isNotEmpty())
                        <x-badge variante="jaune">À valider</x-badge>
                    @else
                        <x-badge variante="rose">Manquant</x-badge>
                    @endif
                </span>
            </div>
        @empty
            <p class="text-[13px] text-muted">Aucune pièce obligatoire définie pour ce régime.</p>
        @endforelse

        <div class="mt-4 bandeau bandeau--info">
            <x-icone nom="info" :taille="16" />
            <span>{{ $documentsValides }} sur {{ $documentsRequis->count() }} pièces obligatoires validées.</span>
        </div>
    </x-carte>
</div>
