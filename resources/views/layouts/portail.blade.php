<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">

    <title>@yield('titre', 'Espace client') · Jalon</title>

    <script>
        (function () {
            try {
                if (localStorage.getItem('jalon-theme') === 'sombre') {
                    document.documentElement.dataset.theme = 'dark';
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/global.css', 'resources/css/agence.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ui-animations.css') }}">
</head>
<body>
    <div class="portail">
        <div class="portail-cadre">
            <header class="portail-entete">
                <span class="portail-marque">
                    <x-logo :hauteur="22" />
                </span>

                <div class="segmented" role="group" aria-label="Thème de l'interface">
                    <button type="button" data-theme-option="clair" aria-pressed="true">Clair</button>
                    <button type="button" data-theme-option="sombre" aria-pressed="false">Sombre</button>
                </div>
            </header>

            @yield('contenu')

            <footer class="portail-pied">
                <span>{{ isset($client) ? $client->nom : 'Jalon · Port de Lomé' }}</span>

                <div class="flex items-center gap-3">
                    @yield('pied')

                    @auth
                        <form method="POST" action="{{ route('deconnexion') }}">
                            @csrf
                            <button type="submit">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('connexion') }}">Se connecter</a>
                    @endauth
                </div>
            </footer>
        </div>
    </div>

    <x-toasts />
    <script src="{{ asset('js/ui-animations.js') }}" defer></script>
</body>
</html>
