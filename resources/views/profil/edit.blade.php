@extends('layouts.app')

@section('titre', 'Mon profil')
@section('sous-titre', "Informations personnelles, distinctes de celles de l'agence")

@section('contenu')
    <div class="grid gap-5">
        <x-carte titre="Informations générales" divise>
            <div class="flex items-center gap-4 mb-7">
                @if ($utilisateur->photo_path)
                    <img src="{{ IlluminateSupportFacadesStorage::url($utilisateur->photo_path) }}" alt="" class="avatar" style="object-fit: cover;">
                @else
                    <span class="avatar" style="width:56px;height:56px;font-size:16px">{{ $utilisateur->initiales }}</span>
                @endif

                <div>
                    <p class="text-[13.5px] font-medium">Photo de profil</p>

                    <form method="POST" action="{{ route('profil.photo') }}" enctype="multipart/form-data" class="mt-1">
                        @csrf
                        <label class="lien-action cursor-pointer" for="photo">
                            {{ $utilisateur->photo_path ? 'Remplacer la photo' : 'Changer la photo' }}
                        </label>
                        <input type="file" id="photo" name="photo" accept="image/png,image/jpeg" class="hidden"
                               onchange="this.form.requestSubmit()">
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('profil.update') }}">
                @csrf
                @method('PUT')

                <div class="groupe-champs">
                    <x-champ nom="nom" label="Nom complet" :valeur="$utilisateur->nom" requis />
                    <x-champ nom="email" label="E-mail" type="email" :valeur="$utilisateur->email" requis />
                    <x-champ nom="telephone" label="Téléphone" :valeur="$utilisateur->telephone" placeholder="+228 90 00 00 00" />
                    <div class="champ">
                        <span class="champ-label">Rôle dans l'application</span>
                        <input type="text" value="{{ $utilisateur->role_label }}" disabled>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-bouton type="submit">Mettre à jour les coordonnées</x-bouton>
                </div>
            </form>
        </x-carte>

        <x-carte titre="Mot de passe" sous-titre="Assurez la sécurité de votre compte avec un mot de passe robuste" divise>
            <form method="POST" action="{{ route('profil.mot-de-passe') }}">
                @csrf
                @method('PUT')

                <div class="groupe-champs">
                    <x-champ nom="mot_de_passe_actuel" label="Mot de passe actuel" type="password" placeholder="••••••••••••" autocomplete="current-password" requis />
                    <x-champ nom="password" label="Nouveau mot de passe" type="password" placeholder="8 car. min., 1 chiffre" autocomplete="new-password" requis />
                    <x-champ nom="password_confirmation" label="Confirmation" type="password" placeholder="Retapez le mot de passe" autocomplete="new-password" requis />
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-bouton type="submit">Modifier le mot de passe</x-bouton>
                    <x-bouton type="reset" variante="contour">Annuler</x-bouton>
                </div>
            </form>
        </x-carte>

        <x-carte titre="Préférences" divise>
            <form method="POST" action="{{ route('profil.preferences') }}">
                @csrf
                @method('PUT')

                <div class="groupe-champs">
                    <div class="champ">
                        <span class="champ-label">Langue de l'interface</span>
                        <select name="langue" disabled>
                            <option>Français</option>
                        </select>
                        <span class="aide">D'autres langues seront proposées ultérieurement.</span>
                    </div>

                    <div class="champ">
                        <span class="champ-label" for="theme">Thème d'affichage</span>
                        <select name="theme" id="theme">
                            <option value="clair" @selected(($preferences['theme'] ?? 'clair') === 'clair')>Clair</option>
                            <option value="sombre" @selected(($preferences['theme'] ?? 'clair') === 'sombre')>Sombre</option>
                        </select>
                    </div>
                </div>

                <div class="mt-2">
                    <x-bascule
                        nom="notifications_email"
                        intitule="Notifications par e-mail"
                        precision="Recevoir un récapitulatif quotidien des alertes de franchise et d'échéances"
                        :actif="(bool) ($preferences['notifications_email'] ?? true)"
                    />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-bouton type="submit">Enregistrer les préférences</x-bouton>
                </div>
            </form>
        </x-carte>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const theme = document.getElementById('theme');

            if (theme) {
                theme.addEventListener('change', () => {
                    const bouton = document.querySelector(`[data-theme-option="${theme.value}"]`);
                    if (bouton) {
                        bouton.click();
                    }
                });
            }
        })();
    </script>
@endpush
