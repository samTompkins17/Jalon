@php
    $alerteCouleur = fn ($gravite) => match ($gravite->value) {
        'critique' => 'rose',
        'haute' => 'ambre',
        'moyenne' => 'jaune',
        default => null,
    };
@endphp

<div class="grid gap-5">
    <x-carte etiquette="Surveillance" titre="Alertes liées au dossier">
        <x-slot:actions>
            <a href="{{ route('alertes.index') }}" class="lien-action">Centre d'alertes</a>
        </x-slot:actions>

        @forelse ($dossier->alertes->sortByDesc('date_alerte') as $alerte)
            <div class="ligne-info">
                <div class="min-w-0">
                    <p class="titre">{{ $alerte->titre }}</p>
                    <p class="detail">{{ $alerte->description }}</p>
                </div>

                <div class="droite flex items-center gap-2">
                    @if (! $alerte->lue)
                        <span class="point" style="color: var(--c-brand)"></span>
                    @endif
                    <x-badge :variante="$alerteCouleur($alerte->gravite)">{{ ucfirst($alerte->gravite->value) }}</x-badge>
                    <span class="text-[11.5px] text-muted">{{ $alerte->date_alerte?->translatedFormat('j M') }}</span>
                </div>
            </div>
        @empty
            <x-etat-vide icone="alertes" titre="Aucune alerte" texte="Aucune alerte n'est rattachée à ce dossier pour le moment." />
        @endforelse
    </x-carte>

    <p class="text-[12.5px] text-muted">
        Les alertes de surestarie sont créées automatiquement à J-2, J-1 et J0 de la fin de franchise, sans doublon.
    </p>
</div>
