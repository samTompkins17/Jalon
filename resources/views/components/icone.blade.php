@props(['nom' => 'point', 'taille' => 18, 'trait' => 1.75])

@php
    $traits = [
        'tableau' => '<rect x="3" y="3" width="7" height="7" rx="1.6"/><rect x="14" y="3" width="7" height="7" rx="1.6"/><rect x="14" y="14" width="7" height="7" rx="1.6"/><rect x="3" y="14" width="7" height="7" rx="1.6"/>',
        'dossiers' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'alertes' => '<path d="m10.29 3.86-8.1 14.02A1.5 1.5 0 0 0 3.48 20h17.04a1.5 1.5 0 0 0 1.3-2.12l-8.1-14.02a1.5 1.5 0 0 0-2.6 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'conteneurs' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'clients' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'facturation' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h4"/>',
        'equipe' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'exports' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        'parametres' => '<path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/>',
        'deconnexion' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
        'recherche' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>',
        'cloche' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'fermer' => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'chevron-bas' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-droit' => '<path d="m9 18 6-6-6-6"/>',
        'fleche-haut' => '<path d="M12 19V5"/><path d="m5 12 7-7 7 7"/>',
        'fleche-bas' => '<path d="M12 5v14"/><path d="m19 12-7 7-7-7"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'copier' => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'calendrier' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/>',
        'document' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        'paiement' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01"/><path d="M18 12h.01"/>',
        'filtre' => '<path d="M22 3H2l8 9.46V19l4 2v-8.54z"/>',
        'telecharger' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        'oeil' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
        'crayon' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
        'corbeille' => '<path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'menu' => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 16v-4"/><path d="M12 8h.01"/>',
        'attention' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/>',
        'navire' => '<path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2s2.5 2 5 2 2.5-2 5-2c1.3 0 1.9.5 2.5 1"/><path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/><path d="M12 2v3"/><path d="m4 13 8-3 8 3"/>',
        'utilisateur' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'horloge' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'point' => '<circle cx="12" cy="12" r="4" fill="currentColor" stroke="none"/>',
    ];

    $contenu = $traits[$nom] ?? $traits['point'];
@endphp

<svg
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    width="{{ $taille }}"
    height="{{ $taille }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="{{ $trait }}"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>{!! $contenu !!}</svg>
