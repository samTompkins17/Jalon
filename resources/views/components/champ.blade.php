@props([
    'nom',
    'label' => null,
    'type' => 'text',
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

    <input
        id="{{ $nom }}"
        name="{{ $nom }}"
        type="{{ $type }}"
        value="{{ old($nom, $valeur) }}"
        @if ($requis) required @endif
        {{ $attributes->merge(['autocomplete' => 'off']) }}
    >

    @if ($aide && ! $erreur)
        <span class="aide">{{ $aide }}</span>
    @endif

    @if ($erreur)
        <span class="erreur" role="alert">{{ $erreur }}</span>
    @endif
</label>
