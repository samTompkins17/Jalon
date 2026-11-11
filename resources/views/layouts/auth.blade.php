<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('titre', 'Connexion') · Jalon</title>

    <script>
        (function () {
            try {
                var theme = localStorage.getItem('jalon-theme');
                if (theme === 'sombre') {
                    document.documentElement.dataset.theme = 'dark';
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/global.css', 'resources/js/app.js'])
</head>
<body class="page-auth">
    <div class="auth-entete">
        <div class="segmented" role="group" aria-label="Thème de l'interface">
            <button type="button" data-theme-option="clair" aria-pressed="true">Clair</button>
            <button type="button" data-theme-option="sombre" aria-pressed="false">Sombre</button>
        </div>
    </div>

    <main class="auth-carte @yield('classe-carte')">
        <div class="auth-marque">
            <x-logo :hauteur="30" />
        </div>

        @yield('contenu')
    </main>

    <x-toasts />
</body>
</html>
