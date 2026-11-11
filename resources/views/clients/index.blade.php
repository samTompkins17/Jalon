@extends('layouts.app')

@section('titre', 'Clients')

@section('sous-titre')
    {{ $clients->total() }} client{{ $clients->total() > 1 ? 's' : '' }}
@endsection

@section('actions')
    @if (auth()->user()->permission('gerer_clients'))
        <x-bouton href="{{ route('clients.creation') }}" icone="plus">Nouveau client</x-bouton>
    @endif
@endsection

@section('contenu')
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        <x-carte titre="Aperçu des clients" sous-titre="Sélectionnez un client pour afficher sa fiche.">
            <div class="tableau-scroll">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Client</th>
                            <th scope="col">Type</th>
                            <th scope="col">Contact</th>
                            <th scope="col" class="cellule-droite">Dossiers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clients as $client)
                            <tr @class(['ligne-selectionnee' => $selection?->id === $client->id])>
                                <td>
                                    <a href="{{ route('clients.index', ['q' => $recherche ?: null, 'client' => $client->code]) }}"
                                       class="reference" style="display:inline-flex;align-items:center;gap:8px">
                                        @if ($selection?->id === $client->id)
                                            <span class="point" style="color: var(--c-brand)"></span>
                                        @endif
                                        {{ $client->nom }}
                                    </a>
                                </td>
                                <td class="cellule-compacte">{{ ucfirst($client->type) }}</td>
                                <td class="cellule-compacte">{{ $client->contact_nom ?? '—' }}</td>
                                <td class="cellule-droite">{{ $client->dossiers_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <x-etat-vide
                                        icone="clients"
                                        titre="Aucun client"
                                        texte="Modifiez la recherche ou créez un nouveau client."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($clients->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color: var(--c-line-soft)">
                    <p class="text-[12.5px] text-muted">
                        {{ $clients->firstItem() }} à {{ $clients->lastItem() }} sur {{ $clients->total() }} client{{ $clients->total() > 1 ? 's' : '' }}
                    </p>

                    <x-pagination :paginateur="$clients" />
                </div>
            @endif
        </x-carte>

        @if ($selection)
            @php
                $dernier = $selection->dossiers->first();
                $compteActif = $selection->utilisateur?->estActif() ?? false;
            @endphp

            <x-carte class="fiche-conteneur">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    @if ($compteActif)
                        <x-badge variante="vert">● Compte client actif</x-badge>
                    @else
                        <x-badge>Compte client inactif</x-badge>
                    @endif

                    <a href="{{ route('clients.show', $selection) }}" class="lien-action" style="color: var(--c-brand)">
                        Ouvrir la fiche →
                    </a>
                </div>

                <h2 class="mt-3 text-[22px] font-semibold" style="letter-spacing:-0.02em">{{ $selection->nom }}</h2>
                <p class="text-[12.5px] text-muted">{{ ucfirst($selection->type) }} · {{ $selection->ville }}, {{ $selection->pays }}</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="stat-mini">
                        <p class="etiquette">Dossiers</p>
                        <p class="valeur">{{ $selection->dossiers_count }}</p>
                        <p class="text-[11.5px] text-muted">dont {{ $retard }} en retard</p>
                    </div>

                    <div class="stat-mini">
                        <p class="etiquette">Solde dû</p>
                        <p class="valeur">{{ \App\Support\Format::nombre($selection->solde_du) }}</p>
                        <p class="text-[11.5px] text-muted">FCFA · facturé</p>
                    </div>
                </div>

                <p class="etiquette mt-5">Coordonnées de contact</p>

                <div class="mt-2">
                    <div class="ligne-info">
                        <span class="cle">Contact principal</span>
                        <span class="valeur">{{ $selection->contact_nom ?? '—' }}</span>
                    </div>
                    <div class="ligne-info">
                        <span class="cle">Téléphone</span>
                        <span class="valeur">{{ $selection->telephone ?? '—' }}</span>
                    </div>
                    <div class="ligne-info">
                        <span class="cle">WhatsApp</span>
                        <span class="valeur" style="color: var(--c-teal)">{{ $selection->whatsapp ?? '—' }}</span>
                    </div>
                    <div class="ligne-info">
                        <span class="cle">E-mail</span>
                        <span class="valeur">{{ $selection->email ?? '—' }}</span>
                    </div>
                </div>

                @if ($dernier)
                    <p class="etiquette mt-5">Activité récente</p>

                    <div class="ligne-info mt-2" style="border:1px solid var(--c-line-soft);border-radius:10px;padding:12px 14px;background:var(--c-white)">
                        <div class="min-w-0">
                            <p class="text-[11.5px] text-muted">Dernier dossier</p>
                            <a href="{{ route('dossiers.show', $dernier) }}" class="reference">{{ $dernier->reference }}</a>
                            <p class="text-[12px] text-muted">{{ $dernier->regime?->nom }} · jalon {{ $dernier->jalon_actuel }} sur 9</p>
                        </div>

                        @if (($jours = $dernier->conteneurUrgent()?->jours_restants) !== null)
                            <x-badge :variante="$jours <= 0 ? 'rose' : ($jours === 1 ? 'ambre' : 'jaune')">
                                {{ $jours <= 0 ? 'J0 franchise' : 'J-'.$jours }}
                            </x-badge>
                        @else
                            <x-badge :variante="$dernier->etat === 'livre' ? 'vert' : 'bleu'">{{ $dernier->etat_label }}</x-badge>
                        @endif
                    </div>
                @endif

                <div class="mt-5 grid gap-2">
                    <x-bouton href="{{ route('dossiers.creation', ['client' => $selection->code]) }}" bloc>
                        Nouveau dossier pour ce client
                    </x-bouton>

                    <form method="POST" action="{{ route('clients.invitation', $selection) }}">
                        @csrf
                        <x-bouton type="submit" variante="contour" bloc>Renvoyer l'invitation</x-bouton>
                    </form>
                </div>
            </x-carte>
        @endif
    </div>
@endsection
