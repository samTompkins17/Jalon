@props(['paginateur'])

@if ($paginateur->hasPages())
    @php
        $courante = $paginateur->currentPage();
        $derniere = $paginateur->lastPage();
        $debut = max(1, $courante - 1);
        $fin = min($derniere, $courante + 1);

        if ($courante <= 2) {
            $fin = min($derniere, 4);
        }

        if ($courante >= $derniere - 1) {
            $debut = max(1, $derniere - 3);
        }
    @endphp

    <nav class="pagination" aria-label="Pagination">
        @if ($paginateur->onFirstPage())
            <span class="page-lien page-lien--fleche page-lien--inactif">Précédent</span>
        @else
            <a href="{{ $paginateur->previousPageUrl() }}" class="page-lien page-lien--fleche" rel="prev">Précédent</a>
        @endif

        @for ($page = $debut; $page <= $fin; $page++)
            <a
                href="{{ $paginateur->url($page) }}"
                @class(['page-lien', 'actif' => $page === $courante])
                @if ($page === $courante) aria-current="page" @endif
            >{{ $page }}</a>
        @endfor

        @if ($paginateur->hasMorePages())
            <a href="{{ $paginateur->nextPageUrl() }}" class="page-lien page-lien--fleche" rel="next">Suivant</a>
        @else
            <span class="page-lien page-lien--fleche page-lien--inactif">Suivant</span>
        @endif
    </nav>
@endif
