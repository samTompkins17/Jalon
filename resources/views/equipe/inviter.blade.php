@extends('layouts.app')

@section('titre', 'Inviter un employé')
@section('sous-titre', 'L\'employé reçoit un lien valable 7 jours pour activer son accès')

@section('fil')
    <a href="{{ route('equipe.index') }}">‹ Équipe</a>
    <span class="separateur-fil">/</span>
    <span>Inviter un employé</span>
@endsection

@section('contenu')
    <form method="POST" action="{{ route('equipe.invitation.store') }}" class="grid gap-5">
        @csrf

        <x-carte titre="Profil du collaborateur" divise>
            <div class="groupe-champs">
                <x-champ nom="nom" label="Nom complet" placeholder="Ex. Mawuli Tossou" requis />
                <x-champ nom="email" label="E-mail professionnel" type="email" placeholder="m.tossou@agence-transit.tg" requis />
                <x-champ nom="poste" label="Fonction dans l'agence" placeholder="Ex. Déclarant en douane junior" />
                <x-champ-select nom="role" label="Rôle attribué" requis>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', 'transitaire') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </x-champ-select>
            </div>
        </x-carte>

        <x-carte titre="Permissions détaillées" sous-titre="Ajustez les droits d'accès aux modules pour ce collaborateur" divise>
            <div>
                @foreach ($permissions as $cle => $libelle)
                    <x-bascule
                        nom="permissions[]"
                        identifiant="perm-{{ $cle }}"
                        :intitule="$libelle"
                        :precision="$descriptions[$cle]"
                        :valeur="$cle"
                        :actif="old('permissions') ? in_array($cle, old('permissions'), true) : ($presets['transitaire'][$cle] ?? false)"
                        data-permission="{{ $cle }}"
                    />
                @endforeach
            </div>
        </x-carte>

        <div class="flex flex-wrap items-center gap-3">
            <x-bouton type="submit" icone="utilisateur">Envoyer l'invitation</x-bouton>
            <x-bouton href="{{ route('equipe.index') }}" variante="contour">Annuler</x-bouton>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const presets = @json($presets);
            const role = document.getElementById('role');

            if (! role) {
                return;
            }

            role.addEventListener('change', () => {
                const preset = presets[role.value] || {};

                document.querySelectorAll('[data-permission]').forEach((champ) => {
                    const cle = champ.dataset.permission;
                    champ.checked = Boolean(preset[cle]);
                });
            });
        })();
    </script>
@endpush
