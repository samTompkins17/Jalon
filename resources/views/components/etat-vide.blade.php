@props(['icone' => 'dossiers', 'titre' => 'Aucun élément', 'texte' => null])

<div class="etat-vide">
    <span class="pictogramme">
        <x-icone :nom="$icone" :taille="20" />
    </span>
    <p class="titre">{{ $titre }}</p>
    @if ($texte)
        <p class="texte">{{ $texte }}</p>
    @endif
    @isset($action)
        <div class="mt-2">{{ $action }}</div>
    @endisset
</div>
