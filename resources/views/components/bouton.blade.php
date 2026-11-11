@props([
    'variante' => 'primaire',
    'href' => null,
    'type' => 'button',
    'icone' => null,
    'taille' => null,
    'bloc' => false,
])

@php
    $classe = collect([
        'btn',
        'btn-'.$variante,
        $taille === 'petit' ? 'btn-petit' : null,
        $bloc ? 'btn-bloc' : null,
    ])->filter()->implode(' ');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classe]) }}>
        @if ($icone)
            <x-icone :nom="$icone" :taille="16" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classe]) }}>
        @if ($icone)
            <x-icone :nom="$icone" :taille="16" />
        @endif
        {{ $slot }}
    </button>
@endif
