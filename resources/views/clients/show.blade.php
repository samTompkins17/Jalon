@extends('layouts.app')

@section('titre-onglet', $client->nom)

@section('titre', $client->nom)

@section('sous-titre')
    {{ ucfirst($client->type) }} · {{ $client->code }}
    @if ($client->utilisateur?->estActif())
        · <span style="color: var(--c-vert);font-weight:500">● Compte client actif</span>
    @endif
@endsection

@section('fil')
    <a href="{{ route('clients.index') }}">Clients</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">{{ $client->nom }}</span>
@endsection

@section('actions')
    <div class="flex flex-wrap items-center gap-2">
        <x-bouton href="{{ route('dossiers.creation', ['client' => $client->code]) }}" icone="plus">
            Nouveau dossier pour ce client
        </x-bouton>
    </div>
@endsection

@section('contenu')
    @php
        $couleurStatutFacture = fn ($statut) => match ($statut->value) {
            'payee' => 'vert',
            'partielle' => 'jaune',
            default => 'rose',
        };
    @endphp

    <div class="fiche-conteneur grid gap-5">
        {{-- Indicateurs --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="stat-mini">
                <p class="etiquette">Dossiers</p>
                <p class="valeur">{{ $client->dossiers->count() }}<small>dossiers</small></p>
                <p @class(['text-[12px] mt-1', 'font-medium' => $dossiersEnRetard > 0]) @if ($dossiersEnRetard > 0) style="color: var(--c-rose)" @endif>
                    {{ $dossiersEnRetard > 0 ? $dossiersEnRetard.' en retard de franchise' : 'Aucun retard de franchise' }}
                </p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Facturé (FCFA)</p>
                <p class="valeur">{{ \App\Support\Format::nombre($totalFacture) }}</p>
                <p class="text-[12px] text-muted mt-1">Total des montants facturés émis</p>
            </div>

            <div class="stat-mini">
                <p class="etiquette">Solde dû (FCFA)</p>
                <p class="valeur" @if ($soldeDu > 0) style="color: var(--c-brand)" @endif>
                    {{ \App\Support\Format::nombre($soldeDu) }}
                </p>
                <p class="text-[12px] text-muted mt-1">Reste à recouvrer sur les opérations</p>
            </div>
        </div>

        {{-- Coordonnées --}}
        <x-carte titre="Coordonnées" sous-titre="Informations de facturation et contacts administratifs">
            <x-slot:actions>
                @if (auth()->user()->permission('gerer_clients'))
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('clients.edit', $client) }}" class="lien-action">Modifier</a>

                        <form method="POST" action="{{ route('clients.archiver', $client) }}"
                              data-confirmer="Archiver le client {{ $client->nom }} ? Ses dossiers resteront consultables."
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lien-action" style="color: var(--c-rose)">Archiver</button>
                        </form>
                    </div>
                @endif
            </x-slot:actions>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="etiquette">Contact</p>
                    <p class="mt-1 text-[13.5px] font-medium">{{ $client->contact_nom ?? '—' }}</p>
                    <p class="text-[12px] text-muted">{{ $client->fonction ?? 'Contact principal' }}</p>
                </div>

                <div>
                    <p class="etiquette">Téléphone</p>
                    <p class="mt-1 text-[13.5px] font-medium">{{ $client->telephone ?? '—' }}</p>
                    <p class="text-[12px] text-muted">Ligne directe</p>
                </div>

                <div>
                    <p class="etiquette">WhatsApp</p>
                    <p class="mt-1 text-[13.5px] font-medium" style="color: var(--c-teal)">{{ $client->whatsapp ?? '—' }}</p>
                    <p class="text-[12px] text-muted">Canal privilégié pour alertes</p>
                </div>

                <div>
                    <p class="etiquette">E-mail</p>
                    <p class="mt-1 text-[13.5px] font-medium">{{ $client->email ?? '—' }}</p>
                    <p class="text-[12px] text-muted">Factures et avis d'arrivée</p>
                </div>
            </div>

            @if ($client->adresse || $client->notes)
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @if ($client->adresse)
                        <div>
                            <p class="etiquette">Adresse</p>
                            <p class="mt-1 text-[13px]">{{ $client->adresse }}, {{ $client->ville }} ({{ $client->pays }})</p>
                        </div>
                    @endif

                    @if ($client->notes)
                        <div>
                            <p class="etiquette">Notes internes</p>
                            <p class="mt-1 text-[13px] text-muted">{{ $client->notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </x-carte>

        {{-- Dossiers --}}
        <x-carte titre="Dossiers" sous-titre="Historique des opérations de dédouanement et transit">
            <x-slot:actions>
                <x-badge>{{ $client->dossiers->count() }} dossier{{ $client->dossiers->count() > 1 ? 's' : '' }}</x-badge>
            </x-slot:actions>

            <div class="tableau-scroll">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Référence</th>
                            <th scope="col">Jalon</th>
                            <th scope="col">État</th>
                            <th scope="col">Marge (FCFA)</th>
                            <th scope="col" class="cellule-droite">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($client->dossiers as $dossier)
                            <tr>
                                <td>
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="reference">{{ $dossier->reference }}</a>
                                    <p class="text-[12px] text-muted">{{ $dossier->regime?->nom }} · Port de Lomé</p>
                                </td>
                                <td>
                                    <x-badge :variante="$dossier->jalon_actuel >= 9 ? 'vert' : 'rose'">{{ $dossier->jalon_actuel }} sur 9</x-badge>
                                    <p class="text-[12px] text-muted">{{ $dossier->jalon_nom }}</p>
                                </td>
                                <td>
                                    <x-badge :variante="match ($dossier->etat) {
                                        'livre' => 'vert',
                                        'en_retard' => 'rose',
                                        default => 'bleu',
                                    }">{{ $dossier->etat_label }}</x-badge>
                                </td>
                                <td class="cellule-compacte">{{ \App\Support\Format::nombre($dossier->marge_reelle) }}</td>
                                <td class="cellule-droite">
                                    <a href="{{ route('dossiers.show', $dossier) }}" class="lien-action">Ouvrir →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-etat-vide icone="dossiers" titre="Aucun dossier" texte="Ouvrez un premier dossier pour ce client." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-carte>

        {{-- Factures --}}
        <x-carte titre="Factures et paiements" sous-titre="Suivi comptable des règlements et créances client">
            <x-slot:actions>
                @if (auth()->user()->peutFacturer() || auth()->user()->permission('facturer'))
                    <a href="{{ route('facturation.creation', ['client' => $client->code]) }}" class="lien-action" style="color: var(--c-brand)">
                        + Créer une facture
                    </a>
                @endif
            </x-slot:actions>

            <div class="tableau-scroll">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Facture</th>
                            <th scope="col">Dossier</th>
                            <th scope="col">Total FCFA</th>
                            <th scope="col">Payé</th>
                            <th scope="col" class="cellule-droite">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($client->factures as $facture)
                            <tr>
                                <td>
                                    <a href="{{ route('facturation.show', $facture) }}" class="reference">{{ $facture->numero }}</a>
                                    <p class="text-[12px] text-muted">Émise le {{ $facture->date_facture?->translatedFormat('j M') }}</p>
                                </td>
                                <td>
                                    @if ($facture->dossier)
                                        <a href="{{ route('dossiers.show', $facture->dossier) }}" class="reference">{{ $facture->dossier->reference }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="cellule-compacte">{{ \App\Support\Format::nombre($facture->total) }}</td>
                                <td class="cellule-compacte">{{ \App\Support\Format::nombre($facture->montant_paye) }}</td>
                                <td class="cellule-droite">
                                    <x-badge :variante="$couleurStatutFacture($facture->statut)">{{ $facture->statut->label() }}</x-badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-etat-vide icone="facturation" titre="Aucune facture" texte="Les factures émises apparaîtront ici." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-carte>

        {{-- Invitation au portail --}}
        <x-carte titre="Espace client" sous-titre="Accès en lecture seule aux dossiers, documents et factures">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    @if ($client->utilisateur?->estActif())
                        <x-badge variante="vert">Compte actif</x-badge>
                        <p class="mt-2 text-[12.5px] text-muted">{{ $client->email }} peut consulter ses dossiers.</p>
                    @elseif ($invitation)
                        <x-badge variante="jaune">Invitation en attente</x-badge>
                        <p class="mt-2 text-[12.5px] text-muted">
                            Lien valable jusqu'au {{ $invitation->expires_at->translatedFormat('j M Y') }}.
                        </p>
                    @else
                        <x-badge>Portail non activé</x-badge>
                        <p class="mt-2 text-[12.5px] text-muted">Aucun accès au portail client pour le moment.</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('clients.invitation', $client) }}">
                    @csrf
                    <x-bouton type="submit" variante="contour">
                        {{ $invitation ? "Renvoyer l'invitation" : "Inviter à l'espace client" }}
                    </x-bouton>
                </form>
            </div>
        </x-carte>
    </div>
@endsection
