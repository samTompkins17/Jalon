@extends('layouts.auth')

@section('titre', 'Mot de passe oublié')

@section('contenu')
    <h1 class="auth-titre" style="font-size: clamp(30px, 4.2vw, 44px)">Mot de passe oublié ?</h1>
    <p class="auth-sous-titre">
        Indiquez l'adresse de votre compte
    </p>

    <form method="POST" action="{{ route('mot-de-passe.envoyer') }}">
        @csrf

        <x-champ
            nom="email"
            label="E-mail"
            type="email"
            placeholder="nom@agence-lome.tg"
            autocomplete="username"
            requis
            autofocus
        />

        <div class="mt-8">
            <x-bouton type="submit" bloc>Envoyer le lien</x-bouton>
        </div>
    </form>

    <p class="auth-pied">
        <a href="{{ route('connexion') }}">Revenir à la connexion</a>
    </p>
@endsection
