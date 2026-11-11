<?php

namespace App\Http\Controllers;

use App\Mail\ReinitialisationMotDePasseMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MotDePasseController extends Controller
{
    public function demande(): View
    {
        return view('auth.mot-de-passe-oublie');
    }

    public function envoyer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
        ]);

        $utilisateur = User::where('email', mb_strtolower($donnees['email']))->first();

        if ($utilisateur) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $utilisateur->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            Mail::to($utilisateur->email)->send(new ReinitialisationMotDePasseMail($utilisateur, $token));
        }

        return redirect()
            ->route('mot-de-passe.demande')
            ->with('info', 'Si un compte correspond à cette adresse, un lien de réinitialisation vient d\'être envoyé.');
    }

    public function formulaire(Request $request, string $token): View
    {
        return view('auth.reinitialiser', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reinitialiser(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $ligne = DB::table('password_reset_tokens')->where('email', $donnees['email'])->first();

        $valide = $ligne
            && Hash::check($donnees['token'], $ligne->token)
            && $ligne->created_at
            && now()->diffInMinutes($ligne->created_at) <= 60;

        if (! $valide) {
            return back()->withErrors([
                'email' => 'Ce lien de réinitialisation est invalide ou a expiré.',
            ])->onlyInput('email');
        }

        $utilisateur = User::where('email', $donnees['email'])->firstOrFail();

        $utilisateur->forceFill([
            'password' => $donnees['password'],
            'password_changed_at' => now(),
        ])->save();

        DB::table('password_reset_tokens')->where('email', $donnees['email'])->delete();

        return redirect()
            ->route('connexion')
            ->with('succes', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');
    }
}
