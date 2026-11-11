@props(['variante' => null, 'petit' => false])

@php
    $classe = collect([
        'badge',
        $variante ? 'badge--'.$variante : null,
        $petit ? 'badge--petit' : null,
    ])->filter()->implode(' ');
@endphp

<span {{ $attributes->merge(['class' => $classe]) }}>{{ $slot }}</span>
