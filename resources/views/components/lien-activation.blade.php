@props(['cle' => 'lien_invitation'])

@if (session($cle))
    <div class="bandeau bandeau--info">
        <x-icone nom="info" :taille="16" />
        <div class="min-w-0 flex-1">
            <p class="font-medium">Lien d'activation à transmettre</p>
            <p class="mt-1 break-all text-[12px]" style="color: var(--c-ink-2)">{{ session($cle) }}</p>
        </div>
        <button type="button" class="btn btn-petit btn-contour" data-copier="{{ session($cle) }}">
            <x-icone nom="copier" :taille="14" />
            Copier
        </button>
    </div>
@endif
