<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Jalon — suivi de transit et de douane au port de Lomé.">

    <title>@hasSection('titre-onglet')@yield('titre-onglet')@else @yield('titre', 'Tableau de bord') @endif · Jalon</title>

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

    @vite(['resources/css/global.css', 'resources/css/agence.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/ui-animations.css') }}">
</head>
<body>
    @php
        $utilisateur = auth()->user();
        $dossiers = $compteurs['dossiers'] ?? null;
        $alertes = $compteurs['alertes'] ?? null;
        $conteneurs = $compteurs['conteneurs'] ?? null;

        $liens = [
            ['nom' => 'Tableau de bord', 'route' => 'tableau-de-bord', 'motif' => 'tableau-de-bord', 'icone' => 'tableau', 'roles' => ['administrateur', 'transitaire', 'comptable']],
            ['nom' => 'Dossiers', 'route' => 'dossiers.index', 'motif' => 'dossiers.*', 'icone' => 'dossiers', 'compteur' => $dossiers, 'roles' => ['administrateur', 'transitaire', 'comptable']],
            ['nom' => 'Alertes', 'route' => 'alertes.index', 'motif' => 'alertes.*', 'icone' => 'alertes', 'compteur' => $alertes, 'alerte' => true, 'roles' => ['administrateur', 'transitaire', 'comptable']],
            ['nom' => 'Conteneurs', 'route' => 'conteneurs.index', 'motif' => 'conteneurs.*', 'icone' => 'conteneurs', 'compteur' => $conteneurs, 'roles' => ['administrateur', 'transitaire']],
        ];

        $liensGestion = [
            ['nom' => 'Clients', 'route' => 'clients.index', 'motif' => 'clients.*', 'icone' => 'clients', 'roles' => ['administrateur', 'transitaire']],
            ['nom' => 'Facturation', 'route' => 'facturation.index', 'motif' => 'facturation.*', 'icone' => 'facturation', 'roles' => ['administrateur', 'comptable'], 'solo' => true],
            ['nom' => 'Équipe', 'route' => 'equipe.index', 'motif' => 'equipe.*', 'icone' => 'equipe', 'roles' => ['administrateur'], 'solo' => true],
            ['nom' => 'Exports & Rapports', 'route' => 'exports.index', 'motif' => 'exports.*', 'icone' => 'exports', 'roles' => ['administrateur', 'comptable']],
            ['nom' => 'Paramètres', 'route' => 'parametres.index', 'motif' => 'parametres.*', 'icone' => 'parametres', 'roles' => ['administrateur'], 'solo' => true],
        ];

        $visible = fn (array $lien) => in_array($utilisateur?->role?->value, $lien['roles'], true)
            || (! empty($lien['solo']) && $utilisateur?->est_transitaire_solo);
    @endphp

    <div class="app-shell" data-menu-ouvert="false">
        <div class="voile-menu" data-fermer-menu></div>

        <aside class="sidebar" aria-label="Navigation principale">
            <a href="{{ route('tableau-de-bord') }}" class="marque">
                <x-logo :hauteur="24" />
            </a>

            <div class="agence-bloc">
                <p class="nom truncate">{{ $utilisateur?->agence?->nom ?? 'Agence de démonstration' }}</p>
                <p class="lieu">{{ $utilisateur?->agence?->ville ?? 'Lomé' }} ({{ $utilisateur?->agence?->pays ?? 'Togo' }})</p>
            </div>

            <div class="segmented" role="group" aria-label="Thème de l'interface">
                <button type="button" data-theme-option="clair" aria-pressed="true">Clair</button>
                <button type="button" data-theme-option="sombre" aria-pressed="false">Sombre</button>
            </div>

            <nav class="sidebar-navigation">
                <p class="nav-section">Principal</p>

                @foreach ($liens as $lien)
                    @continue(! $visible($lien))

                    <a
                        href="{{ route($lien['route']) }}"
                        @class(['nav-lien', 'actif' => request()->routeIs($lien['motif'])])
                        @if (request()->routeIs($lien['motif'])) aria-current="page" @endif
                    >
                        <x-icone :nom="$lien['icone']" :taille="17" />
                        <span>{{ $lien['nom'] }}</span>
                        @if (! empty($lien['compteur']))
                            <span @class(['nav-compteur', 'nav-compteur--alerte' => ! empty($lien['alerte'])])>{{ $lien['compteur'] }}</span>
                        @endif
                    </a>
                @endforeach

                <p class="nav-section">Gestion</p>

                @foreach ($liensGestion as $lien)
                    @continue(! $visible($lien))

                    <a
                        href="{{ route($lien['route']) }}"
                        @class(['nav-lien', 'actif' => request()->routeIs($lien['motif'])])
                        @if (request()->routeIs($lien['motif'])) aria-current="page" @endif
                    >
                        <x-icone :nom="$lien['icone']" :taille="17" />
                        <span>{{ $lien['nom'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="sidebar-pied">
                <a href="{{ route('profil.edit') }}" class="sidebar-utilisateur">
                    @if ($utilisateur?->photo_path)
                        <img src="{{ $utilisateur->urlPhoto() }}" alt="" class="avatar avatar--photo">
                    @else
                        <span class="avatar">{{ $utilisateur?->initiales }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="nom truncate">{{ $utilisateur?->nom }}</p>
                        <p class="role truncate">{{ $utilisateur?->poste ?: $utilisateur?->role_label }}</p>
                    </div>
                </a>

                <form method="POST" action="{{ route('deconnexion') }}">
                    @csrf
                    <button type="submit" class="lien-quitter w-full text-left">
                        <x-icone nom="deconnexion" :taille="17" />
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="contenu">
            <div class="contenu-interieur">
                <header class="en-tete-page">
                    <div class="flex items-start gap-3 min-w-0">
                        <button type="button" class="bouton-menu shrink-0" data-bascule-menu aria-label="Ouvrir le menu">
                            <x-icone nom="menu" :taille="20" />
                        </button>

                        <div class="min-w-0">
                            @hasSection('fil')
                                <div class="fil-ariane">@yield('fil')</div>
                            @endif

                            <h1 class="titre-page">@yield('titre', 'Tableau de bord')</h1>
                            @hasSection('sous-titre')
                                <p class="sous-titre-page">@yield('sous-titre')</p>
                            @endif
                        </div>
                    </div>

                    <div class="actions-page">
                        @php
                            [$actionRecherche, $inviteRecherche] = match (true) {
                                request()->routeIs('conteneurs.*') => [route('conteneurs.index'), 'Conteneur, dossier ou client…'],
                                request()->routeIs('clients.*') => [route('clients.index'), 'Nom, code ou contact…'],
                                request()->routeIs('facturation.*') => [route('facturation.index'), 'Facture, dossier ou client…'],
                                default => [route('dossiers.index'), 'Dossier, conteneur ou clé…'],
                            };
                        @endphp

                        <form class="recherche" method="GET" action="{{ $actionRecherche }}" role="search">
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="{{ $inviteRecherche }}"
                                aria-label="Rechercher un dossier, un conteneur ou un client"
                                data-recherche
                            >
                            <span class="raccourci" aria-hidden="true">⌘K</span>
                        </form>

                        @yield('actions')
                    </div>
                </header>

                @yield('contenu')
            </div>
        </main>
    </div>

    <x-toasts />

    @stack('scripts')
    <script src="{{ asset('js/ui-animations.js') }}" defer></script>
</body>
</html>
