@props([
    'hauteur' => 26,
    'alt' => 'Jalon',
])

<span {{ $attributes->merge(['class' => 'logo']) }}>
    <img src="{{ asset('img/logo-jalon-clair.png') }}" alt="{{ $alt }}" class="logo-image logo-image--clair" style="height: {{ $hauteur }}px">
    <img src="{{ asset('img/logo-jalon-sombre.png') }}" alt="" aria-hidden="true" class="logo-image logo-image--sombre" style="height: {{ $hauteur }}px">
</span>
