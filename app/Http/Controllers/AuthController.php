<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.connexion');
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'password.required' => 'Renseignez votre mot de passe.',
        ]);

        $cle = 'connexion:'.mb_strtolower($donnees['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($cle, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($cle).' secondes.',
            ]);
        }

        if (! Auth::attempt([
            'email' => $donnees['email'],
            'password' => $donnees['password'],
        ], $request->boolean('remember'))) {
            RateLimiter::hit($cle, 60);

            throw ValidationException::withMessages([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ]);
        }

        RateLimiter::clear($cle);
        $request->session()->regenerate();

        $utilisateur = $request->user();

        if (! $utilisateur->estActif()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte n\'est pas encore activé. Contactez votre administrateur.',
            ]);
        }

        $utilisateur->forceFill(['derniere_connexion_at' => now()])->saveQuietly();

        if ($utilisateur->estSuperAdmin()) {
            return redirect()->route('plateforme.index');
        }

        if (! $utilisateur->agence?->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'L’accès à cette agence est suspendu. Contactez le support.',
            ]);
        }

        if ($utilisateur->estClient()) {
            return redirect()->intended(route('portail.tableau-de-bord'));
        }

        return redirect()->intended(route('tableau-de-bord'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('connexion')->with('info', 'Vous êtes déconnecté.');
    }
}
