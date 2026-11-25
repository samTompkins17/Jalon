<?php

namespace App\Http\Controllers;

use App\Models\DemandeDemo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DemoController extends Controller
{
    /** Créneaux proposés, heure de Lomé (UTC). */
    public const HEURES = ['08:00', '09:00', '10:00', '11:00', '14:00', '15:00', '16:00', '17:00'];

    public function create(): View
    {
        return view('demo', ['heures' => self::HEURES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'agence' => ['nullable', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'telephone' => ['required', 'string', 'max:40'],
            'format' => ['required', Rule::in(['visio', 'presentiel'])],
            'date' => ['required', 'date', 'after:today'],
            'heure' => ['required', Rule::in(self::HEURES)],
            'message' => ['nullable', 'string', 'max:1000'],
        ], [
            'nom.required' => 'Renseignez votre nom.',
            'email.required' => 'Renseignez votre adresse e-mail.',
            'email.email' => "Cette adresse e-mail n'est pas valide.",
            'telephone.required' => 'Renseignez un numéro de téléphone.',
            'format.required' => 'Choisissez la forme de la démonstration.',
            'date.required' => 'Choisissez une date.',
            'date.after' => 'Choisissez une date à partir de demain.',
            'heure.required' => 'Choisissez un créneau horaire.',
            'heure.in' => 'Ce créneau horaire n’est pas proposé.',
            'message.max' => 'Le message est limité à 1000 caractères.',
        ]);

        $creneau = Carbon::createFromFormat('Y-m-d H:i', $donnees['date'].' '.$donnees['heure']);

        DemandeDemo::create([
            'nom' => $request->string('nom')->trim()->toString(),
            'agence' => $request->string('agence')->trim()->toString() ?: null,
            'email' => mb_strtolower($request->string('email')->trim()->toString()),
            'telephone' => $request->string('telephone')->trim()->toString(),
            'format' => $donnees['format'],
            'creneau_at' => $creneau,
            'message' => $request->string('message')->trim()->toString() ?: null,
        ]);

        return redirect()
            ->route('demo')
            ->with('creneau', $creneau->translatedFormat('l j F Y à H:i'))
            ->with('info', 'Votre demande de démonstration est enregistrée. Nous revenons vers vous pour confirmer le créneau.');
    }
}
