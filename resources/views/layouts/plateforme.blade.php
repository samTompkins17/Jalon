<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Jalon — console d’administration de la plateforme.">
    <title>@yield('titre', 'Administration plateforme') · Jalon</title>
    <script>
        (function () {
            try {
                if (localStorage.getItem('jalon-theme') === 'sombre') {
                    document.documentElement.dataset.theme = 'dark';
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/global.css', 'resources/css/plateforme.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ui-animations.css') }}">
</head>
<body class="console-page min-h-screen bg-page text-ink">
    <header class="console-barre">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-3.5 sm:px-8">
            <a href="{{ route('plateforme.index') }}" class="flex min-w-0 items-center gap-4">
                <x-logo :hauteur="25" />
                <span class="hidden border-l pl-4 text-sm font-medium text-muted sm:inline" style="border-color: var(--c-line-fort)">
                    Console plateforme
                </span>
            </a>

            <div class="flex flex-wrap items-center gap-3">
                <div class="segmented" role="group" aria-label="Thème de l’interface">
                    <button type="button" data-theme-option="clair" aria-pressed="true">Clair</button>
                    <button type="button" data-theme-option="sombre" aria-pressed="false">Sombre</button>
                </div>

                <div class="console-utilisateur">
                    @if (auth()->user()->photo_path)
                        <img src="{{ auth()->user()->urlPhoto() }}" alt="" class="console-avatar console-avatar--photo">
                    @else
                        <span class="console-avatar">{{ auth()->user()->initiales }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="nom truncate">{{ auth()->user()->nom }}</p>
                        <p class="role truncate">Super administrateur</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('deconnexion') }}">
                    @csrf
                    <button class="btn btn-petit btn-contour" type="submit">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8 sm:py-10">
        <div class="mb-6">
            <p class="etiquette">Administration</p>
            <h1 class="titre-page">@yield('titre', 'Administration plateforme')</h1>
            <p class="sous-titre-page">Demandes d’accès, agences et supervision du réseau Jalon.</p>
        </div>

        @yield('contenu')
    </main>

    <x-toasts />
    <script src="{{ asset('js/ui-animations.js') }}" defer></script>
</body>
</html>
