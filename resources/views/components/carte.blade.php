@props([
    'etiquette' => null,
    'titre' => null,
    'sousTitre' => null,
    'actions' => null,
    'divise' => false,
])

@php
    $entete = $etiquette || $titre || $sousTitre || $actions;
@endphp

<section {{ $attributes->merge(['class' => 'carte']) }}>
    <div class="carte-corps">
        @if ($entete)
            <div @class(['carte-entete', 'carte-entete--divise' => $divise])>
                <div>
                    @if ($etiquette)
                        <p class="etiquette">{{ $etiquette }}</p>
                    @endif
                    @if ($titre)
                        <h2 class="carte-titre">{{ $titre }}</h2>
                    @endif
                    @if ($sousTitre)
                        <p class="carte-sous-titre">{{ $sousTitre }}</p>
                    @endif
                </div>
                @if ($actions)
                    <div class="shrink-0">{{ $actions }}</div>
                @endif
            </div>
        @endif

        <div @class(['mt-5' => $entete])>
            {{ $slot }}
        </div>
    </div>
</section>
