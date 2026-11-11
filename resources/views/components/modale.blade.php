@props([
    'id',
    'titre',
    'sousTitre' => null,
    'large' => false,
])

<div
    class="modale"
    id="{{ $id }}"
    data-ouvert="false"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-titre"
>
    <div class="modale-fond"></div>

    <div @class(['modale-boite', 'modale-boite--large' => $large]) role="document">
        <div class="modale-entete">
            <div>
                <h2 class="modale-titre" id="{{ $id }}-titre">{{ $titre }}</h2>
                @if ($sousTitre)
                    <p class="modale-sous-titre">{{ $sousTitre }}</p>
                @endif
            </div>

            <button type="button" class="btn btn-icone" data-modale-fermer aria-label="Fermer la fenêtre">
                <x-icone nom="fermer" :taille="18" />
            </button>
        </div>

        {{ $slot }}

        @isset($pied)
            <div class="modale-pied">{{ $pied }}</div>
        @endisset
    </div>
</div>
