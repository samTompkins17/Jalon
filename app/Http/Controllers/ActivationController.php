<?php

namespace App\Http\Controllers;

use App\Enums\StatutActivation;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ActivationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = Invitation::with(['user', 'agence'])->where('token', $token)->first();

        if (! $invitation || $invitation->estExpiree() || ! $invitation->user) {
            return view('auth.activation', [
                'invitation' => $invitation,
                'invalide' => true,
            ]);
        }

        return view('auth.activation', [
            'invitation' => $invitation,
            'invalide' => false,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::with('user')->where('token', $token)->first();

        abort_unless($invitation && ! $invitation->estExpiree() && $invitation->user, 404);

        $donnees = $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'password.required' => 'Choisissez un mot de passe.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $membre = $invitation->user;

        $membre->forceFill([
            'password' => $donnees['password'],
            'statut_activation' => StatutActivation::Actif,
            'password_changed_at' => now(),
            'email_verified_at' => now(),
        ])->save();

        $invitation->forceFill(['accepted_at' => now()])->save();

        Auth::login($membre);
        $request->session()->regenerate();

        return redirect()
            ->route('tableau-de-bord')
            ->with('succes', 'Bienvenue '.$membre->nom.' ! Votre compte est activé.');
    }
}
