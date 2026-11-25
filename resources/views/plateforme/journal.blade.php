@extends('layouts.plateforme')

@section('titre', 'Journal des actions')

@section('contenu')
    @php
        $libellesActions = [
            'demande_approuvee' => 'Demande approuvée',
            'demande_refusee' => 'Demande refusée',
            'agence_suspendue' => 'Agence suspendue',
            'agence_reactivee' => 'Agence réactivée',
            'notes_modifiees' => 'Notes modifiées',
        ];
        $libellesCibles = [
            'demande_agence' => 'Demande d’agence',
            'agence' => 'Agence',
        ];
        $variantesActions = [
            'demande_approuvee' => 'vert',
            'demande_refusee' => 'rose',
            'agence_suspendue' => 'rose',
            'agence_reactivee' => 'vert',
            'notes_modifiees' => 'ambre',
        ];
    @endphp

    <section class="grid gap-4" aria-labelledby="journal-titre">
        <div class="console-section-titre">
            <div>
                <p class="etiquette">Supervision</p>
                <h2 id="journal-titre" class="carte-titre">Journal des actions de la plateforme</h2>
                <p class="carte-sous-titre">
                    Historique des décisions et modifications effectuées par les super administrateurs.
                </p>
            </div>
            <span class="console-pastille">
                <x-icone nom="horloge" :taille="13" />
                {{ $entrees->total() }} entrée{{ $entrees->total() > 1 ? 's' : '' }}
            </span>
        </div>

        <div class="carte overflow-hidden">
            <div class="tableau-scroll">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Date / heure</th>
                            <th scope="col">Auteur</th>
                            <th scope="col">Action</th>
                            <th scope="col">Cible</th>
                            <th scope="col">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($entrees as $entree)
                            <tr>
                                <td class="cellule-compacte text-[12px] text-muted">
                                    {{ $entree->created_at->translatedFormat('j M Y à H:i') }}
                                </td>
                                <td>
                                    @if ($entree->user)
                                        <p class="font-medium">{{ $entree->user->nom }}</p>
                                        <p class="text-[12px] text-muted">{{ $entree->user->email }}</p>
                                    @else
                                        <span class="text-[12px] text-muted">Utilisateur supprimé</span>
                                    @endif
                                </td>
                                <td>
                                    <x-badge :variante="$variantesActions[$entree->action] ?? 'ambre'" petit>
                                        {{ $libellesActions[$entree->action] ?? $entree->action }}
                                    </x-badge>
                                </td>
                                <td class="cellule-compacte">
                                    @if ($entree->cible_libelle)
                                        <p class="font-medium">{{ $entree->cible_libelle }}</p>
                                        @if ($entree->cible_type)
                                            <p class="text-[12px] text-muted">{{ $libellesCibles[$entree->cible_type] ?? $entree->cible_type }}</p>
                                        @endif
                                    @else
                                        <span class="text-[12px] text-muted">—</span>
                                    @endif
                                </td>
                                <td class="cellule-compacte text-[12px] text-muted">
                                    {{ $entree->details ?: '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-etat-vide icone="document" titre="Journal vide"
                                                 texte="Les actions de la plateforme apparaîtront ici au fil des décisions." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($entrees->hasPages())
                <div class="carte-corps border-t" style="border-color: var(--c-line-soft)">
                    <x-pagination :paginateur="$entrees" />
                </div>
            @endif
        </div>
    </section>
@endsection
