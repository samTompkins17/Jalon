@extends('layouts.auth')

@section('titre', 'Créer mon espace')
@section('classe-carte', 'auth-carte--large')

@section('contenu')
    <h1 class="auth-titre" style="font-size: clamp(32px, 4.4vw, 44px)">Créer mon espace</h1>
    <p class="auth-sous-titre">Gestion et suivi de dossiers douaniers au Port de Lomé</p>

    <p class="champ-label">Vous êtes…</p>

    <div class="choix-types">
        <a href="{{ route('inscription') }}" @class(['choix-type', 'actif' => $type === 'solo'])>
            <span class="choix-type-titre">Transitaire solo</span>
            <span class="choix-type-desc">Je travaille seul</span>
        </a>

        <a href="{{ route('inscription', ['vous' => 'agence']) }}" @class(['choix-type', 'actif' => $type === 'agence'])>
            <span class="choix-type-titre">Agence</span>
            <span class="choix-type-desc">J'ai une équipe</span>
        </a>
    </div>

    @if ($type === 'agence')
        <form method="POST" action="{{ route('inscription.agence') }}" novalidate>
            @csrf

            <x-champ
                nom="nom_agence"
                label="Nom de l'agence"
                placeholder="Ex. Lomé Transit & Consignation"
                requis
            />

            <x-champ
                nom="nom_contact"
                label="Nom du responsable"
                placeholder="Ex. Adjo Kouassi"
                autocomplete="name"
                requis
            />

            <x-champ
                nom="email"
                label="E-mail professionnel"
                type="email"
                placeholder="contact@lome-transit.tg"
                autocomplete="email"
                requis
            />

            <x-champ
                nom="telephone"
                label="Téléphone"
                type="tel"
                placeholder="+228 90 00 00 00"
                autocomplete="tel"
                requis
            />

            <x-champ
                nom="numero_agrement"
                label="N° d’agrément (facultatif)"
                placeholder="Ex. CD-2024-TG-042"
            />

            <div class="encart-info">
                Votre demande sera examinée par l’administration plateforme. Après approbation,
                vous recevrez un lien pour activer le compte administrateur de votre agence.
            </div>

            <div class="mt-8">
                <x-bouton type="submit" bloc>Envoyer la demande d’agence</x-bouton>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('inscription.store') }}" novalidate>
            @csrf

            <x-champ
                nom="nom"
                label="Nom complet"
                placeholder="Ex. Adjo Kouassi"
                autocomplete="name"
                requis
            />

            <x-champ
                nom="email"
                label="E-mail"
                type="email"
                placeholder="adjo@transit-lome.tg"
                autocomplete="email"
                requis
            />

            <x-champ
                nom="telephone"
                label="Téléphone"
                type="tel"
                placeholder="+228 90 00 00 00"
                autocomplete="tel"
                requis
            />

            <x-champ
                nom="password"
                label="Mot de passe"
                type="password"
                placeholder="8 caractères minimum"
                autocomplete="new-password"
                requis
            />

            <x-champ
                nom="numero_agrement"
                label="N° d'agrément (facultatif)"
                placeholder="Ex. CD-2024-TG-042"
            />

            <div class="mt-8">
                <x-bouton type="submit" bloc>Créer mon espace</x-bouton>
            </div>
        </form>
    @endif

    <p class="auth-pied">
        @if ($type === 'agence')
            Déjà un compte ?
            <a href="{{ route('connexion') }}">Se connecter</a>
        @else
            Vous pourrez passer en agence plus tard.
            <br>
            Déjà un compte ?
            <a href="{{ route('connexion') }}">Se connecter</a>
        @endif
    </p>

    <p class="mt-10 text-center text-[13px] text-muted">
        Données de démonstration : noms, numéros et montants sont fictifs.
    </p>
@endsection
