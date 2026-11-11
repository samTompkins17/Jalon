@props([
    'nom',
    'identifiant' => null,
    'intitule' => null,
    'precision' => null,
    'actif' => false,
    'valeur' => '1',
])

@php
    $id = $identifiant ?? \Illuminate\Support\Str::slug(str_replace('[]', '', $nom));
@endphp

<label class="ligne-reglage" for="{{ $id }}">
    <span>
        <span class="intitule">{{ $intitule }}</span>
        @if ($precision)
            <span class="precision block">{{ $precision }}</span>
        @endif
    </span>

    <span class="bascule">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $nom }}"
            value="{{ $valeur }}"
            @checked((bool) old($nom, $actif))
            {{ $attributes }}
        >
        <span class="piste" aria-hidden="true"></span>
    </span>
</label>
