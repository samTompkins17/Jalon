@extends('layouts.auth')

@section('titre', 'Connexion')

@section('contenu')
    <h1 class="auth-titre">Connexion</h1>
    <p class="auth-sous-titre">Suivi de transit et douane · Port de Lomé</p>

    <form method="POST" action="{{ route('connexion.store') }}" novalidate>
        @csrf

        <x-champ
            nom="email"
            label="E-mail"
            type="email"
            placeholder="nom@agence-lome.tg"
            requis
            autocomplete="username"
            autofocus
        />

        <x-champ
            nom="password"
            label="Mot de passe"
            type="password"
            placeholder="••••••••••••"
            requis
            autocomplete="current-password"
        />

        <div class="mt-8">
            <x-bouton type="submit" bloc>Se connecter</x-bouton>
        </div>
    </form>

    <p class="auth-pied">
        Mot de passe oublié ?
        <a href="{{ route('mot-de-passe.demande') }}">Réinitialiser mon compte</a>
    </p>

    <p class="auth-pied" style="margin-top: 14px">
        Pas encore de compte ?
        <a href="{{ route('inscription') }}">Créer mon espace</a>
    </p>

    <p class="mt-10 text-center text-[13px] text-muted">
        Données de démonstration : noms, numéros et montants sont fictifs.
    </p>
@endsection
