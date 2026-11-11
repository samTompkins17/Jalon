@extends('layouts.portail')

@php
    $prenom = \Illuminate\Support\Str::of($client->contact_nom ?: $client->nom)->trim()->explode(' ')->first();
@endphp

@section('titre', 'Espace client')

@section('contenu')
    <h1 class="portail-titre">Bonjour, {{ $prenom }}</h1>
    <p class="portail-sous-titre">Vos dossiers, en lecture seule.</p>

    <div class="mt-4">
        @forelse ($dossiers as $dossier)
            @php
                $jours = $dossier->conteneurUrgent()?->jours_restants;
                $livre = $dossier->etat === 'livre';
                $classeBarre = $livre ? 'termine' : ($dossier->etat === 'en_retard' ? '' : 'en-cours');
            @endphp

            <a href="{{ route('portail.dossier', $dossier) }}" class="portail-carte">
                <div class="ref">
                    <span>{{ $dossier->reference }}</span>

                    @if ($jours !== null && $jours <= 0)
                        <x-badge variante="rose">● Franchise : J0</x-badge>
                    @elseif ($livre)
                        <x-badge variante="vert">● Terminé</x-badge>
                    @else
                        <x-badge variante="jaune">● En cours</x-badge>
                    @endif
                </div>

                <p class="etape">
                    @if ($livre)
                        Livré
                    @else
                        Étape {{ $dossier->jalon_actuel }} sur 9 · {{ $dossier->jalon_nom }}
                    @endif
                </p>

                <div class="portail-barre">
                    <span class="{{ $classeBarre }}" style="width: {{ max(4, round($dossier->jalon_actuel / 9 * 100)) }}%"></span>
                </div>
            </a>
        @empty
            <div class="portail-note">
                <x-icone nom="info" :taille="15" />
                <span>Aucun dossier n'est encore rattaché à votre compte. Contactez votre transitaire.</span>
            </div>
        @endforelse
    </div>

    <div class="portail-note">
        <x-icone nom="info" :taille="15" />
        <span>Documents et factures de chaque dossier : consultation uniquement.</span>
    </div>
@endsection
