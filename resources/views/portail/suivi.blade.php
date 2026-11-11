@extends('layouts.portail')

@section('titre', 'Suivre un dossier')

@section('contenu')
    <h1 class="portail-titre">Suivre un dossier</h1>
    <p class="portail-sous-titre">Saisissez le code de suivi communiqué par votre transitaire.</p>

    <form method="GET" action="{{ route('suivi') }}" class="mt-4 flex flex-wrap gap-2">
        <label class="sr-only" for="code">Code de suivi</label>
        <input
            id="code"
            type="text"
            name="code"
            value="{{ $code }}"
            placeholder="Ex. K7M2-9QX4"
            required
            autocomplete="off"
            style="flex:1;min-width:200px;height:42px;padding:0 14px;border:1px solid var(--c-champ-bord);border-radius:8px;background:var(--c-white);color:var(--c-ink);font-size:14px;letter-spacing:0.04em;text-transform:uppercase"
        >
        <x-bouton type="submit">Rechercher</x-bouton>
    </form>

    @if ($erreur)
        <div class="portail-note" style="color: var(--c-rose)">
            <x-icone nom="attention" :taille="15" />
            <span>{{ $erreur }}</span>
        </div>
    @endif

    @if ($dossier)
        @php
            $jours = $dossier->conteneurUrgent()?->jours_restants;
            $termine = $dossier->jalon_actuel >= 9;
        @endphp

        <div class="portail-carte" style="margin-top:18px">
            <div class="ref">
                <span>{{ $dossier->reference }}</span>

                @if ($jours !== null && $jours <= 0)
                    <x-badge variante="rose">● Franchise : J0</x-badge>
                @elseif ($termine)
                    <x-badge variante="vert">● Terminé</x-badge>
                @else
                    <x-badge variante="jaune">● En cours</x-badge>
                @endif
            </div>

            <p class="etape">
                @if ($termine)
                    Livré
                @else
                    Étape {{ $dossier->jalon_actuel }} sur 9 · {{ $dossier->jalon_nom }}
                @endif
            </p>

            <div class="portail-barre">
                <span class="{{ $termine ? 'termine' : 'en-cours' }}" style="width: {{ max(4, round($dossier->jalon_actuel / 9 * 100)) }}%"></span>
            </div>

            <div class="mt-4 grid gap-2 text-[12.5px]">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-muted">Régime douanier</span>
                    <span class="font-medium">{{ $dossier->regime?->nom ?? '—' }}</span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-muted">Arrivée prévue</span>
                    <span class="font-medium">{{ $dossier->date_arrivee_prevue?->translatedFormat('j M Y') ?? '—' }}</span>
                </div>

                @if ($dossier->conteneurs->isNotEmpty())
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-muted">Conteneurs</span>
                        <span class="font-medium">{{ $dossier->conteneurs->pluck('type')->unique()->implode(', ') }}</span>
                    </div>
                @endif

                @if ($dossier->jalon_suivant)
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-muted">Prochaine étape</span>
                        <span class="font-medium">{{ $dossier->jalon_suivant->nom }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="portail-note">
            <x-icone nom="info" :taille="15" />
            <span>
                Le détail des documents et des factures est disponible dans votre espace client.
            </span>
        </div>
    @endif
@endsection

@section('pied')
    <a href="{{ route('connexion') }}">Espace client</a>
@endsection
