@extends('layouts.app')

@php
    $creation = $mode === 'creation';
@endphp

@section('titre-onglet', $creation ? 'Nouveau client' : 'Modifier '.$client->nom)

@section('titre', $creation ? 'Nouveau client' : 'Modifier le client')

@section('sous-titre', $creation ? 'Un client peut avoir plusieurs dossiers.' : $client->code.' · '.$client->nom)

@section('fil')
    <a href="{{ route('clients.index') }}">Clients</a>
    <span class="separateur-fil">/</span>
    <span style="color: var(--c-brand)">{{ $creation ? 'Nouveau client' : $client->nom }}</span>
@endsection

@section('contenu')
    <form method="POST" action="{{ $creation ? route('clients.store') : route('clients.update', $client) }}">
        @csrf
        @if (! $creation)
            @method('PUT')
        @endif

        <div class="grid gap-5" style="max-width:980px">
            <x-carte titre="Informations générales">
                <div class="groupe-champs">
                    <x-champ-select nom="type" label="Type de client" requis>
                        @foreach (['entreprise' => 'Entreprise', 'particulier' => 'Particulier'] as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(old('type', $client->type) === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </x-champ-select>

                    <x-champ nom="nom" label="Nom ou raison sociale" placeholder="Ex. Société Adjo Import SARL" requis :valeur="$client->nom" />

                    <x-champ nom="contact_nom" label="Contact principal" placeholder="Ex. Adjo Mensah" requis :valeur="$client->contact_nom" />

                    <x-champ nom="fonction" label="Fonction" placeholder="Ex. Directrice Générale / Responsable Achats" :valeur="$client->fonction" />

                    <x-champ nom="telephone" label="Téléphone" placeholder="+228 90 00 00 00" requis :valeur="$client->telephone" />

                    <x-champ nom="whatsapp" label="WhatsApp" placeholder="+228 90 00 00 00 (pour alertes automatiques)" :valeur="$client->whatsapp" />

                    <x-champ nom="email" label="E-mail" type="email" placeholder="contact@entreprise.tg" requis :valeur="$client->email" />

                    <x-champ nom="ville" label="Ville" placeholder="Lomé" :valeur="$client->ville" />

                    <x-champ nom="adresse" label="Adresse" placeholder="Zone Industrielle Portuaire, Boulevard du Mono" :valeur="$client->adresse" style="grid-column:1/-1" />

                    <x-champ-zone nom="notes" label="Notes internes" placeholder="Particularités de dédouanement, régimes habituels, consignes spécifiques…" :valeur="$client->notes" style="grid-column:1/-1" />
                </div>
            </x-carte>

            @if ($creation)
                <x-carte titre="Compte client" sous-titre="Accès en lecture seule au portail de suivi">
                    <label class="flex items-start gap-3" style="cursor:pointer">
                        <input type="checkbox" name="inviter" value="1" @checked(old('inviter')) style="margin-top:3px">
                        <span>
                            <span class="block text-[13.5px] font-medium">Envoyer une invitation d'accès à l'espace client</span>
                            <span class="block text-[12.5px] text-muted">
                                Le client reçoit un lien valable 7 jours pour définir son mot de passe et suivre ses dossiers
                                en lecture seule.
                            </span>
                        </span>
                    </label>
                </x-carte>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                <x-bouton type="submit">{{ $creation ? 'Créer le client' : 'Enregistrer les modifications' }}</x-bouton>
                <x-bouton href="{{ $creation ? route('clients.index') : route('clients.show', $client) }}" variante="contour">Annuler</x-bouton>
            </div>
        </div>
    </form>
@endsection
