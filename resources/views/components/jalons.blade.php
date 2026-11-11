@props(['courant' => 1, 'total' => 9])

<span class="jalons" role="img" aria-label="Jalon {{ $courant }} sur {{ $total }}">
    @for ($i = 1; $i <= $total; $i++)
        <span @class(['jalon-trait', 'actif' => $i <= $courant])></span>
    @endfor
</span>
