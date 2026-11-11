<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AgenceActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $utilisateur = $request->user();

        abort_unless($utilisateur && ! $utilisateur->estSuperAdmin() && $utilisateur->agence_id, 403);
        abort_unless($utilisateur->agence?->active, 403, 'L’accès à cette agence est suspendu.');

        return $next($request);
    }
}
