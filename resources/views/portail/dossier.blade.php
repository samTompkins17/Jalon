@extends('layouts.portail')

@section('titre', $dossier->reference)

@section('contenu')
    @php
        $jours = $dossier->conteneurUrgent()?->jours_restants;
        $termine = $dossier->jalon_actuel >= 9;
    @endphp

    <div class="mt-2">
        <a href="{{ route('portail.tableau-de-bord') }}" class="lien-action" style="color: var(--c-ink-2)">
            ← Mes dossiers
        </a>
    </div>

    <div class="mt-4 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="portail-titre" style="margin-top:0">{{ $dossier->reference }}</h1>
            <p class="mt-1 text-[13px]" style="color: var(--c-brand)">
                @if ($termine)
                    Livré
                @else
                    Étape {{ $dossier->jalon_actuel }} sur 9 · {{ $dossier->jalon_nom }}
                @endif
            </p>
        </div>

        @if ($jours !== null && $jours <= 0)
            <x-badge variante="rose">● Franchise : J0</x-badge>
        @elseif ($termine)
            <x-badge variante="vert">● Terminé</x-badge>
        @else
            <x-badge variante="jaune">● En cours</x-badge>
        @endif
    </div>

    <div class="portail-carte" style="margin-top:18px">
        <div class="flex items-center justify-between gap-3">
            <span class="text-[11px] font-semibold uppercase tracking-[0.08em]" style="color: var(--c-muted)">Progression globale</span>
            <span class="text-[12.5px] font-semibold">{{ $dossier->jalon_actuel }} / 9 jalons</span>
        </div>

        <div class="portail-barre">
            <span class="{{ $termine ? 'termine' : 'en-cours' }}" style="width: {{ max(4, round($dossier->jalon_actuel / 9 * 100)) }}%"></span>
        </div>

        <p class="etape">
            @if ($dossier->jalon_suivant)
                Dernière étape restante : {{ $dossier->jalon_suivant->nom }}.
            @else
                Toutes les étapes sont terminées.
            @endif
        </p>
    </div>

    <div class="portail-section">
        <div class="flex items-center justify-between gap-3">
            <h2>Documents partagés</h2>
            <span class="text-[11.5px] text-muted">{{ $dossier->documents->count() }} fichier{{ $dossier->documents->count() > 1 ? 's' : '' }}</span>
        </div>

        @forelse ($dossier->documents as $document)
            <div class="portail-doc">
                <div class="flex items-center gap-3 min-w-0">
                    <x-icone nom="document" :taille="18" />
                    <div class="min-w-0">
                        <p class="nom truncate">{{ $document->type }}</p>
                        <p class="meta">
                            {{ $document->valide ? '● Validé' : 'En cours de vérification' }}
                            · {{ number_format($document->taille / 1024, 0, ',', ' ') }} Ko
                        </p>
                    </div>
                </div>

                <x-bouton href="{{ route('portail.document', [$dossier, $document]) }}" variante="contour" taille="petit" icone="telecharger">
                    Télécharger
                </x-bouton>
            </div>
        @empty
            <div class="portail-note">
                <x-icone nom="info" :taille="15" />
                <span>Aucun document n'est encore partagé pour ce dossier.</span>
            </div>
        @endforelse
    </div>

    <div class="portail-section">
        <h2>Factures</h2>

        @forelse ($dossier->factures as $facture)
            <div class="portail-carte" style="margin-top:8px">
                <div class="ref">
                    <span>{{ $facture->numero }}</span>
                    <x-badge :variante="match ($facture->statut->value) {
                        'payee' => 'vert',
                        'partielle' => 'jaune',
                        default => 'rose',
                    }">{{ $facture->statut->label() }}</x-badge>
                </div>

                <div class="mt-3 grid grid-cols-3 gap-3 text-[12px]">
                    <div>
                        <p class="text-muted">Total</p>
                        <p class="font-semibold">{{ \App\Support\Format::nombre($facture->total) }} FCFA</p>
                    </div>
                    <div>
                        <p class="text-muted">Payé</p>
                        <p class="font-semibold" style="color: var(--c-vert)">{{ \App\Support\Format::nombre($facture->montant_paye) }} FCFA</p>
                    </div>
                    <div>
                        <p class="text-muted">Solde</p>
                        <p class="font-semibold" style="color: var(--c-rose)">{{ \App\Support\Format::nombre($facture->solde) }} FCFA</p>
                    </div>
                </div>
            </div>
        @empty
            <div class="portail-note">
                <x-icone nom="info" :taille="15" />
                <span>Aucune facture émise pour ce dossier.</span>
            </div>
        @endforelse
    </div>

    <div class="portail-note">
        <x-icone nom="info" :taille="15" />
        <span>Consultation uniquement. Pour toute question, contactez votre transitaire.</span>
    </div>
@endsection
