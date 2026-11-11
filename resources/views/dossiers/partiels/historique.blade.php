@php
    $peutReculer = auth()->user()->peutReculerJalon() && $dossier->jalon_actuel > 1;
@endphp

<div class="grid gap-5 lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
    <x-carte etiquette="Traçabilité" titre="Historique du dossier">
        @forelse ($dossier->historique as $entree)
            <div @class(['chronologie-item', 'actif' => $loop->first])>
                <p class="titre-chrono">
                    {{ $entree->action_label }}
                    @if ($entree->jalon)
                        <span class="text-muted">· jalon {{ $entree->jalon }}</span>
                    @endif
                </p>
                <p class="meta-chrono">
                    {{ $entree->date_passage?->translatedFormat('j M Y') }}
                    @if ($entree->auteur)
                        · {{ $entree->auteur->nom }}
                    @endif
                </p>
                @if ($entree->commentaire)
                    <p class="corps-chrono">{{ $entree->commentaire }}</p>
                @endif
            </div>
        @empty
            <x-etat-vide icone="horloge" titre="Aucun historique" texte="Les passages de jalon apparaîtront ici." />
        @endforelse
    </x-carte>

    <div class="grid gap-5 content-start">
        <x-carte etiquette="Avancement" titre="Position actuelle">
            <p class="valeur" style="font-size: 30px; font-weight: 600; letter-spacing: -0.03em; color: var(--c-brand)">
                {{ $dossier->jalon_actuel }} / 9
            </p>
            <p class="carte-sous-titre">{{ $dossier->jalon?->nom }}</p>

            @if ($dossier->jalon?->condition_passage)
                <div class="bandeau mt-4">
                    <x-icone nom="info" :taille="16" />
                    <span>Condition de ce jalon : {{ $dossier->jalon->condition_passage }}</span>
                </div>
            @endif
        </x-carte>

        @if ($peutReculer)
            <x-carte etiquette="Administration" titre="Reculer d'un jalon" sous-titre="Action réservée aux administrateurs. Elle est tracée dans l'historique.">
                <form method="POST" action="{{ route('dossiers.jalon.recul', $dossier) }}"
                      data-confirmer="Ramener ce dossier au jalon {{ $dossier->jalon_actuel - 1 }} ?">
                    @csrf

                    <x-champ-zone nom="motif" label="Motif du recul" placeholder="Ex. Déclaration rejetée par SYDONIA, pièce manquante…" requis />

                    <div class="flex justify-end">
                        <x-bouton type="submit" variante="contour">Revenir au jalon {{ $dossier->jalon_actuel - 1 }}</x-bouton>
                    </div>
                </form>
            </x-carte>
        @endif
    </div>
</div>
