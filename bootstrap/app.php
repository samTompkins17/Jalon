<?php

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(fn () => route('connexion'));
        $middleware->redirectUsersTo(function () {
            $utilisateur = Auth::user();

            return $utilisateur instanceof User && $utilisateur->estSuperAdmin()
                ? route('plateforme.index')
                : route('tableau-de-bord');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
