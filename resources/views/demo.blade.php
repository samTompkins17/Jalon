@extends('layouts.auth')

@section('titre', 'Demander une démo')
@section('classe-carte', 'auth-carte--large')

@section('contenu')
    <style>
        .demo-format { border: 0; margin: 0 0 18px; padding: 0; }
        .demo-format legend { margin-bottom: 8px; padding: 0; }
        .demo-format-choix { display: flex; flex-wrap: wrap; gap: 10px; }
        .demo-format-choix label {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 14px; font-size: .88rem;
            border: 1px solid var(--c-line-fort); border-radius: var(--r-s);
            cursor: pointer;
        }
        .demo-format-choix label:has(input:checked) {
            border-color: var(--c-brand); background: var(--c-page);
        }
        .demo-note { margin-top: 14px; font-size: .8rem; color: var(--c-muted); text-align: center; }
        .demo-retour { margin-top: 22px; text-align: center; font-size: .85rem; }
        .demo-retour a { color: var(--c-brand-texte); }
    </style>

    <h1 class="auth-titre" style="font-size: clamp(27px, 4vw, 34px)">Demander une démo</h1>
    <p class="auth-sous-titre">
        Vingt minutes pour voir Jalon sur un dossier réel — en visioconférence, ou en présentiel à Lomé.
        Choisissez le créneau qui vous convient : nous confirmons ensuite par e-mail ou par téléphone.
    </p>

    @if (session('creneau'))
        <div class="bandeau bandeau--succes" role="status">
            <x-icone nom="check" :taille="16" class="mt-0.5" />
            <div>
                <p class="font-medium">Demande enregistrée</p>
                <p class="mt-1 text-xs">Créneau demandé : {{ session('creneau') }} (heure de Lomé).</p>
            </div>
        </div>
    @endif

    @if ($errors->any() && ! $errors->has('nom') && ! $errors->has('email') && ! $errors->has('telephone'))
        <div class="bandeau bandeau--alerte" role="alert">
            <x-icone nom="attention" :taille="16" class="mt-0.5" />
            <p>{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('demo.store') }}" novalidate>
        @csrf

        <div class="groupe-champs">
            <x-champ nom="nom" label="Nom et prénom" requis autocomplete="name" />
            <x-champ nom="agence" label="Agence ou société" aide="Facultatif" />
        </div>

        <div class="groupe-champs">
            <x-champ nom="email" label="E-mail professionnel" type="email" requis autocomplete="email" />
            <x-champ nom="telephone" label="Téléphone" type="tel" requis autocomplete="tel" />
        </div>

        <fieldset class="demo-format">
            <legend class="champ-label">Forme de la démonstration</legend>
            <div class="demo-format-choix">
                <label>
                    <input type="radio" name="format" value="visio" @checked(old('format', 'visio') === 'visio')>
                    Visioconférence
                </label>
                <label>
                    <input type="radio" name="format" value="presentiel" @checked(old('format') === 'presentiel')>
                    Présentiel à Lomé
                </label>
            </div>
            @error('format')
                <span class="erreur" role="alert">{{ $message }}</span>
            @enderror
        </fieldset>

        <div class="groupe-champs">
            <x-champ nom="date" label="Date souhaitée" type="date" requis
                     min="{{ now()->addDay()->toDateString() }}"
                     valeur="{{ now()->addDay()->toDateString() }}" />
            <x-champ-select nom="heure" label="Heure (Lomé)" requis placeholder="Choisir un créneau">
                @foreach ($heures as $heure)
                    <option value="{{ $heure }}" @selected(old('heure') === $heure)>{{ $heure }}</option>
                @endforeach
            </x-champ-select>
        </div>

        <label class="champ" for="message">
            <span class="champ-label">Votre contexte</span>
            <textarea id="message" name="message" rows="3"
                      placeholder="Taille de l’équipe, volume de dossiers, question à traiter…">{{ old('message') }}</textarea>
            @error('message')
                <span class="erreur" role="alert">{{ $message }}</span>
            @enderror
        </label>

        <x-bouton type="submit" bloc>Envoyer ma demande</x-bouton>

        <p class="demo-note">
            Aucun engagement. Vos coordonnées servent uniquement à préparer cette démonstration.
        </p>
    </form>

    <p class="demo-retour">
        <a href="{{ url('/') }}">Retour à la vitrine Jalon</a>
    </p>
@endsection
