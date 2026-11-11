<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profil.edit', [
            'utilisateur' => $request->user(),
            'preferences' => $request->user()->preferences ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $utilisateur = $request->user();

        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique('users', 'email')->ignore($utilisateur->id)],
            'telephone' => ['nullable', 'string', 'max:40'],
        ], [
            'nom.required' => 'Renseignez votre nom complet.',
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => 'Cette adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée par un autre compte.',
        ]);

        $utilisateur->fill($donnees)->save();

        return redirect()->route('profil.edit')->with('succes', 'Vos coordonnées ont été mises à jour.');
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'photo.required' => 'Choisissez une image.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'Formats acceptés : JPG ou PNG.',
            'photo.max' => 'L\'image ne doit pas dépasser 2 Mo.',
        ]);

        $utilisateur = $request->user();
        $ancien = $utilisateur->photo_path;

        $chemin = $request->file('photo')->store('photos', 'public');

        $utilisateur->forceFill(['photo_path' => $chemin])->save();

        if ($ancien && $ancien !== $chemin) {
            Storage::disk('public')->delete($ancien);
        }

        return redirect()->route('profil.edit')->with('succes', 'Votre photo de profil a été mise à jour.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'mot_de_passe_actuel' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'mot_de_passe_actuel.required' => 'Saisissez votre mot de passe actuel.',
            'password.required' => 'Saisissez le nouveau mot de passe.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        if (! Hash::check($donnees['mot_de_passe_actuel'], $request->user()->password)) {
            return back()->withErrors([
                'mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.',
            ])->onlyInput('mot_de_passe_actuel');
        }

        $request->user()->forceFill([
            'password' => $donnees['password'],
            'password_changed_at' => now(),
        ])->save();

        return redirect()->route('profil.edit')->with('succes', 'Votre mot de passe a été modifié.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'notifications_email' => ['nullable', 'boolean'],
            'theme' => ['nullable', Rule::in(['clair', 'sombre'])],
        ]);

        $preferences = $request->user()->preferences ?? [];

        $preferences['notifications_email'] = $request->boolean('notifications_email');
        $preferences['theme'] = $donnees['theme'] ?? ($preferences['theme'] ?? 'clair');
        $preferences['langue'] = 'fr';

        $request->user()->forceFill(['preferences' => $preferences])->save();

        return redirect()->route('profil.edit')->with('succes', 'Vos préférences ont été enregistrées.');
    }
}
