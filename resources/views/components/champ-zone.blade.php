@props([
    'nom',
    'label' => null,
    'valeur' => null,
    'aide' => null,
    'requis' => false,
])

@php
    $erreur = $errors->first($nom);
@endphp

<label @class(['champ', 'champ--erreur' => (bool) $erreur]) for="{{ $nom }}">
    @if ($label)
        <span class="champ-label">{{ $label }}</span>
    @endif

    <textarea id="{{ $nom }}" name="{{ $nom }}" @if ($requis) required @endif {{ $attributes }}>{{ old($nom, $valeur) }}</textarea>

    @if ($aide && ! $erreur)
        <span class="aide">{{ $aide }}</span>
    @endif

    @if ($erreur)
        <span class="erreur" role="alert">{{ $erreur }}</span>
    @endif
</label>
