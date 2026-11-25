@extends('layouts.anim')
@section('title', 'Démo animations')
@push('styles')
<style>
  body{font-family:system-ui,sans-serif;background:#f6f4fb;margin:0;color:#1d1a2b}
  .layout{display:grid;grid-template-columns:200px 1fr;gap:24px;max-width:1000px;margin:32px auto;padding:0 16px}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}
  .card{background:#fff;border-radius:16px;padding:18px;box-shadow:0 8px 24px -12px rgba(60,40,120,.25)}
  .big{font-size:42px;font-weight:800}
  .filters button{border:0;background:#ece8f7;border-radius:999px;padding:6px 14px;margin-right:6px;cursor:pointer}
  .filters button.is-active{background:var(--anim-accent);color:#fff}
</style>
@endpush
@section('content')
<div class="layout">
  <nav class="rail card" data-rail>
    <a href="#" class="is-active">Accueil</a><a href="#">Équipe</a><a href="#">Stockage</a><a href="#">Paramètres</a>
  </nav>
  <main class="anim-scene">
    <div class="grid">
      <x-anim.arrive class="card"><small>Heures</small><div class="big"><x-anim.counter value="27,26" suffix="h"/></div></x-anim.arrive>
      <x-anim.arrive class="card"><small>Activité</small><x-anim.bars :values="[40,65,30,90,55,70,45]"/></x-anim.arrive>
      <x-anim.arrive class="card"><small>Stockage</small><br><x-anim.ring :percent="68"/></x-anim.arrive>
      <x-anim.arrive class="card" data-tilt><small>Objet 3D</small><div class="anim-orb anim-float" style="margin:12px auto"></div></x-anim.arrive>
    </div>
    <h3>Favoris</h3>
    <div class="filters">
      <button data-filter="all" class="is-active">Tout</button><button data-filter="video">Vidéo</button><button data-filter="doc">Docs</button>
    </div>
    <div class="grid" style="margin-top:12px">
      <div class="card" data-filter-card data-type="video">Vidéo 3D</div>
      <div class="card" data-filter-card data-type="doc">Cahier des charges</div>
      <div class="card" data-filter-card data-type="video">Démo produit</div>
    </div>
  </main>
</div>
@endsection
