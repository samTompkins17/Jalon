<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  <link rel="stylesheet" href="{{ asset('css/ui-animations.css') }}">
  @stack('styles')
</head>
<body>
  @yield('content')
  <script src="{{ asset('js/ui-animations.js') }}" defer></script>
  @stack('scripts')
</body>
</html>
