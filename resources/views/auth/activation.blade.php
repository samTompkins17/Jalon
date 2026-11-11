@extends('layouts.auth')

@section('titre', $invalide ? 'Invitation expirée' : 'Activation du compte')

@section('contenu')
    @if ($invalide)
        <h1 class="auth-titre" style="font-size: clamp(30px, 4vw, 40px)">Invitation expirée</h1>
        <p class="auth-sous-titre">
            Ce lien d'activation n'est plus valable. Demandez à votre administrateur de vous renvoyer une invitation.
        </p>

        <div class="mt-8">
            <x-bouton href="{{ route('connexion') }}" bloc>Retour à la connexion</x-bouton>
        </div>
    @else
        <h1 class="auth-titre" style="font-size: clamp(32px, 4vw, 44px)">Activez votre compte</h1>
        <p class="auth-sous-titre">
            Bienvenue {{ $invitation->user->nom }} · {{ $invitation->email }}<br>
            Choisissez votre mot de passe pour rejoindre {{ $invitation->agence?->nom }}.
        </p>

        <form method="POST" action="{{ route('activation.store', $invitation->token) }}">
            @csrf

            <x-champ
                nom="password"
                label="Mot de passe"
                type="password"
                placeholder="8 caractères minimum, 1 lettre et 1 chiffre"
                autocomplete="new-password"
                requis
            />

            <x-champ
                nom="password_confirmation"
                label="Confirmation du mot de passe"
                type="password"
                placeholder="Retapez le mot de passe"
                autocomplete="new-password"
                requis
            />

            <div class="mt-8">
                <x-bouton type="submit" bloc>Activer mon compte</x-bouton>
            </div>
        </form>

        <p class="auth-pied">
            L'invitation expire le {{ $invitation->expires_at->translatedFormat('j F Y') }}.
        </p>
    @endif
@endsection
