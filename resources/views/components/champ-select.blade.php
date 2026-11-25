@props([
    'nom',
    'label' => null,
    'valeur' => null,
    'aide' => null,
    'requis' => false,
    'placeholder' => null,
])

@php
    $erreur = $errors->first($nom);
@endphp

<label @class(['champ', 'champ--erreur' => (bool) $erreur]) for="{{ $nom }}">
    @if ($label)
        <span class="champ-label">{{ $label }}</span>
    @endif

    <select id="{{ $nom }}" name="{{ $nom }}" @if ($requis) required @endif {{ $attributes }}>
        @if ($placeholder)
            <option value="" disabled>{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>

    @if ($aide && ! $erreur)
        <span class="aide">{{ $aide }}</span>
    @endif

    @if ($erreur)
        <span class="erreur" role="alert">{{ $erreur }}</span>
    @endif
</label>
