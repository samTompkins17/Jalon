@extends('layouts.auth')

@section('titre', 'Nouveau mot de passe')

@section('contenu')
    <h1 class="auth-titre" style="font-size: clamp(30px, 4.2vw, 44px)">Nouveau mot de passe</h1>
    <p class="auth-sous-titre">Choisissez un mot de passe robuste pour sécuriser votre compte.</p>

    <form method="POST" action="{{ route('mot-de-passe.reinitialiser') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-champ
            nom="email"
            label="E-mail"
            type="email"
            :valeur="$email"
            autocomplete="username"
            requis
        />

        <x-champ
            nom="password"
            label="Nouveau mot de passe"
            type="password"
            placeholder="8 caractères minimum, 1 lettre et 1 chiffre"
            autocomplete="new-password"
            requis
        />

        <x-champ
            nom="password_confirmation"
            label="Confirmation"
            type="password"
            placeholder="Retapez le mot de passe"
            autocomplete="new-password"
            requis
        />

        <div class="mt-8">
            <x-bouton type="submit" bloc>Réinitialiser le mot de passe</x-bouton>
        </div>
    </form>

    <p class="auth-pied">
        <a href="{{ route('connexion') }}">Revenir à la connexion</a>
    </p>
@endsection
