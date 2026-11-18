<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** L'espace agence (dossiers, factures, équipe) n'est pas accessible aux comptes clients. */
class EspaceInterne
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->estClient()) {
            return redirect()->route('portail.tableau-de-bord');
        }

        return $next($request);
    }
}
