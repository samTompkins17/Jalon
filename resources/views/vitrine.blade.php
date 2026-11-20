<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jalon · Le suivi de vos dossiers de transit, de la douane à la livraison</title>
<meta name="description" content="Jalon aide les transitaires du Port de Lomé à suivre chaque dossier d’import, à éviter la surestarie et à tenir leurs clients informés.">
<script>
document.documentElement.classList.add('js');
</script>
@vite(['resources/css/global.css', 'resources/js/app.js'])
<style>
html{scroll-behavior:smooth}
body{margin:0;background:var(--c-page);color:var(--c-ink);overflow-x:hidden}
.vt{max-width:1160px;margin:0 auto;padding:0 22px}
.vt a{text-underline-offset:3px}
:root{--ease:cubic-bezier(.2,.7,.1,1)}

/* Mouvement : révélations, texte masqué, marquee */
.js [data-r]{opacity:0;transform:translateY(26px);transition:opacity .9s var(--ease),transform .9s var(--ease);transition-delay:calc(var(--d,0)*90ms)}
.js [data-r].vu{opacity:1;transform:none}
.ln{display:block;overflow:hidden;padding-bottom:.08em}
.ln>span{display:block;transform:translateY(108%);animation:monte 1s var(--ease) forwards;animation-delay:calc(var(--i,0)*130ms + 150ms)}
@keyframes monte{to{transform:none}}
@keyframes pop{from{transform:scale(0);opacity:0}to{transform:scale(1);opacity:1}}
@keyframes derive{0%,100%{transform:translate(0,0)}50%{transform:translate(40px,30px)}}
@keyframes defile{to{transform:translateX(-50%)}}
@keyframes pouls{0%,100%{box-shadow:0 0 0 0 color-mix(in srgb,var(--c-rose) 35%,transparent)}50%{box-shadow:0 0 0 7px transparent}}

/* Navigation */
.vt-nav-barre{position:sticky;top:0;z-index:20;backdrop-filter:blur(14px);background:color-mix(in srgb,var(--c-page) 78%,transparent);border-bottom:1px solid transparent;transition:border-color .3s}
.vt-nav-barre.defile{border-color:var(--c-line)}
.vt-nav{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;flex-wrap:wrap}
.vt-liens{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
.vt-liens a:not(.btn){color:var(--c-ink-2);text-decoration:none;font-weight:500}
.vt-liens a:not(.btn):hover{color:var(--c-brand)}

/* Hero */
.vt-hero{position:relative;display:grid;grid-template-columns:1.1fr .9fr;gap:56px;align-items:center;padding:72px 0 96px}
.vt-fond{position:absolute;inset:-80px -10vw auto;height:560px;z-index:-1;pointer-events:none;overflow:hidden}
.vt-fond i{position:absolute;border-radius:50%;filter:blur(70px);animation:derive 16s ease-in-out infinite}
.vt-fond i:nth-child(1){width:420px;height:420px;left:8%;top:30px;background:color-mix(in srgb,var(--c-brand) 22%,transparent)}
.vt-fond i:nth-child(2){width:340px;height:340px;right:6%;top:120px;background:color-mix(in srgb,var(--c-brand) 12%,var(--c-surface-2));animation-delay:-6s}
.vt-etiq{display:inline-block;padding:5px 13px;border-radius:999px;background:var(--c-brand-soft);color:var(--c-brand);font-weight:600;font-size:14px;margin:0 0 22px}
.vt h1{font-size:clamp(2.5rem,6.2vw,4.9rem);line-height:1.02;letter-spacing:-.04em;margin:0 0 22px;font-weight:700}
.vt h1 em{font-style:normal;color:var(--c-brand)}
.vt-lead{font-size:1.18rem;line-height:1.6;color:var(--c-ink-2);max-width:50ch;margin:0 0 28px}
.vt-actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px}
.vt-suivi{display:flex;gap:8px;max-width:460px}
.vt-suivi input{flex:1;min-width:0;padding:12px 15px;border:1px solid var(--c-champ-bord);border-radius:10px;background:var(--c-white);color:var(--c-ink);font:inherit;text-transform:uppercase}
.vt-suivi input{box-shadow:var(--ombre-creux)}
.vt-suivi input:focus-visible{outline:none;border-color:var(--c-brand);box-shadow:0 0 0 3px var(--c-brand-soft),var(--ombre-creux)}
.vt-aide{font-size:.9rem;color:var(--c-muted);margin:9px 0 0}

/* Carte dossier animée */
.vt-carte-d{background:var(--c-surface);border:1px solid var(--c-line);border-radius:20px;padding:24px;box-shadow:var(--ombre-fine)}
.vt-carte-d-tete{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:14px}
.vt-puce{padding:4px 11px;border-radius:999px;background:var(--c-rose-bg);color:var(--c-rose);font-size:13px;font-weight:600;white-space:nowrap;animation:pouls 2.4s ease-in-out infinite}
.vt-jalons{list-style:none;margin:0;padding:0}
.vt-jalons li{display:flex;align-items:center;gap:13px;padding:7px 0;color:var(--c-muted);font-size:.95rem}
.vt-jalons li::before{content:"";flex:none;width:14px;height:14px;border-radius:50%;border:2px solid var(--c-line-fort)}
.vt-jalons li.fait{color:var(--c-ink)}
.vt-jalons li.fait::before{background:var(--c-brand);border-color:var(--c-brand);animation:pop .5s var(--ease) both;animation-delay:calc(var(--i)*170ms + 900ms)}
.vt-jalons li.actuel{color:var(--c-ink);font-weight:600}
.vt-jalons li.actuel::before{border-color:var(--c-brand);box-shadow:0 0 0 4px var(--c-brand-soft)}
.vt-note{font-size:.8rem;color:var(--c-muted);margin:12px 0 0}

/* Bandeau défilant */
.vt-bande{border-block:1px solid var(--c-line);overflow:hidden;padding:16px 0;-webkit-mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent);mask-image:linear-gradient(90deg,transparent,#000 12%,#000 88%,transparent)}
.vt-piste{display:flex;gap:44px;width:max-content;animation:defile 34s linear infinite;color:var(--c-muted);font-weight:600;letter-spacing:.05em;text-transform:uppercase;font-size:.85rem;white-space:nowrap}
.vt-bande:hover .vt-piste{animation-play-state:paused}

/* Sections */
.vt-section{padding:96px 0}
.vt-eyebrow{color:var(--c-brand);font-weight:600;font-size:.9rem;letter-spacing:.06em;text-transform:uppercase;margin:0 0 12px}
.vt-section h2{font-size:clamp(1.9rem,4vw,3rem);letter-spacing:-.03em;line-height:1.08;margin:0 0 14px;max-width:20ch}
.vt-intro{color:var(--c-muted);max-width:56ch;font-size:1.08rem;line-height:1.6;margin:0 0 36px}
.vt-grille{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:16px}
.vt-c{position:relative;background:var(--c-surface);border:1px solid var(--c-line);border-radius:18px;padding:26px;overflow:hidden;box-shadow:var(--ombre-fine);transition:transform .4s var(--ease),border-color .3s,box-shadow .3s}
.vt-c:hover{transform:translateY(-2px);border-color:var(--c-line-fort);box-shadow:var(--ombre-relief)}
.vt-c::after{content:"";position:absolute;inset:0;border-radius:inherit;pointer-events:none;opacity:0;transition:opacity .3s;background:radial-gradient(280px circle at var(--mx,50%) var(--my,50%),color-mix(in srgb,var(--c-brand) 16%,transparent),transparent 70%)}
.vt-c:hover::after{opacity:1}
.vt-c h3{margin:0 0 8px;font-size:1.12rem}
.vt-c p{margin:0;color:var(--c-ink-2);line-height:1.55}
.vt-n{display:block;font-weight:700;color:var(--c-brand);margin-bottom:14px;font-size:.9rem;letter-spacing:.05em}
.vt-choix{display:flex;flex-direction:column;align-items:flex-start;gap:16px}

/* Parcours : les 9 jalons suivent le défilement */
.pc{display:grid;grid-template-columns:.9fr 1.1fr;gap:56px}
.pc-fixe{position:sticky;top:110px;align-self:start}
.pc-compteur{font-size:clamp(3.5rem,9vw,7rem);font-weight:700;letter-spacing:-.05em;line-height:1;margin:26px 0 14px;color:var(--c-brand);font-variant-numeric:tabular-nums}
.pc-compteur small{font-size:.35em;color:var(--c-muted);letter-spacing:0}
.pc-barre{height:4px;border-radius:4px;background:var(--c-line);overflow:hidden;max-width:340px}
.pc-barre i{display:block;height:100%;width:11%;background:var(--c-brand);border-radius:4px;transition:width .6s var(--ease)}
.pc-liste{list-style:none;margin:0;padding:0}
.pc-liste li{min-height:34vh;display:flex;align-items:center;font-size:clamp(1.6rem,3.6vw,2.6rem);font-weight:700;letter-spacing:-.03em;color:var(--c-line-fort);transition:color .5s,transform .6s var(--ease);transform:translateX(0)}
.pc-liste li.actif{color:var(--c-ink);transform:translateX(14px)}
.pc-liste li span{font-size:.4em;font-weight:600;color:var(--c-muted);width:2.6em;font-variant-numeric:tabular-nums}

/* Aperçu, FAQ, listes */
.vt-c ul{margin:12px 0 0;padding-left:18px;color:var(--c-ink-2);line-height:1.7}
.pv{display:flex;flex-wrap:wrap;gap:10px}.pv span{padding:9px 16px;border:1px dashed var(--c-line-fort);border-radius:999px;color:var(--c-ink-2);font-weight:500}
.fq details{border-bottom:1px solid var(--c-line);padding:18px 0}
.fq summary{cursor:pointer;font-weight:600;font-size:1.08rem;list-style:none;display:flex;justify-content:space-between;gap:16px}
.fq summary::-webkit-details-marker{display:none}
.fq summary::after{content:"+";color:var(--c-brand);font-size:1.5rem;line-height:1;transition:transform .3s}
.fq details[open] summary::after{transform:rotate(45deg)}
.fq p{color:var(--c-ink-2);line-height:1.6;max-width:70ch;margin:12px 0 0}

/* Galerie */
.sh{display:grid;gap:84px;margin-top:20px}
.sh-l{display:grid;grid-template-columns:1.25fr .75fr;gap:44px;align-items:center}
.sh-l:nth-child(even){grid-template-columns:.75fr 1.25fr}.sh-l:nth-child(even) .sh-f{order:2}
.sh-f{border:1px solid var(--c-line);border-radius:18px;background:var(--c-surface);overflow:hidden;box-shadow:var(--ombre-pose)}
.sh-b{display:flex;gap:7px;padding:11px 15px;border-bottom:1px solid var(--c-line);background:var(--c-surface-2)}
.sh-b i{width:10px;height:10px;border-radius:50%;background:var(--c-line-fort)}
.sh-i,.ph-i{display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in}
.sh-i{height:min(46vw,470px);overflow:hidden}
.sh-i img{width:100%;height:100%;object-fit:cover;object-position:top;display:block;transition:transform 1.2s var(--ease)}
.sh-f:hover .sh-i img{transform:scale(1.03)}
.sh-t h3{font-size:clamp(1.4rem,2.6vw,2rem);letter-spacing:-.03em;line-height:1.12;margin:0 0 12px}
.sh-t p:not(.vt-eyebrow){color:var(--c-ink-2);line-height:1.6;margin:0}
.sh-t ul{margin:14px 0 0;padding-left:18px;color:var(--c-muted);line-height:1.7}
.ph{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:28px;align-items:start}
.ph figure{margin:0;text-align:center}
.ph-i{border:1px solid var(--c-line);border-radius:26px;overflow:hidden;box-shadow:var(--ombre-pose);max-width:260px;margin:0 auto 14px}
.ph-i img{display:block;width:100%;height:auto}
.ph figcaption{color:var(--c-muted);font-size:.95rem;line-height:1.5;max-width:30ch;margin:0 auto}.ph figcaption b{color:var(--c-ink)}
.lb{position:fixed;inset:0;z-index:50;background:color-mix(in srgb,#000 82%,transparent);overflow:auto;padding:28px;cursor:zoom-out;animation:fondu .3s}
.lb[hidden]{display:none}.lb img{display:block;max-width:min(1280px,100%);margin:0 auto;border-radius:12px;height:auto}
@keyframes fondu{from{opacity:0}to{opacity:1}}

/* Final */
.vt-final{margin:0 auto 24px;padding:80px 28px;text-align:center;border-radius:28px;background:var(--c-ink);color:var(--c-page);box-shadow:var(--ombre-pose)}
.vt-final h2{margin:0 auto 14px;max-width:18ch;color:var(--c-page)}
.vt-final p{margin:0 auto 26px;max-width:48ch;opacity:.75}
.vt-final .vt-actions{justify-content:center;margin:0}
.vt-pied{padding:22px 0 34px;color:var(--c-muted);font-size:.9rem;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}

@media (max-width:860px){
.vt-hero,.pc,.sh-l,.sh-l:nth-child(even){grid-template-columns:1fr;gap:26px}.sh-l:nth-child(even) .sh-f{order:0}.sh-i{height:300px}.sh{gap:60px}
.vt-hero{padding:40px 0 64px}.vt-suivi{max-width:none}
.pc-fixe{position:static}.pc-liste li{min-height:20vh}
.vt-section{padding:64px 0}
}
@media (prefers-reduced-motion:reduce){
*,*::before,*::after{animation:none!important;transition:none!important}
.js [data-r]{opacity:1;transform:none}.ln>span{transform:none}.vt-piste{width:auto;flex-wrap:wrap}
}
</style>
</head>
<body>
<div class="vt-nav-barre" id="barre"><div class="vt"><div class="vt-nav">
<a href="{{ url('/') }}" aria-label="Jalon, accueil"><x-logo :hauteur="30" /></a>
<nav class="vt-liens" aria-label="Navigation principale">
<a href="#parcours">Parcours</a><a href="#apercu">Aperçu</a><a href="#fonctions">Fonctions</a><a href="#faq">FAQ</a>
<a href="{{ route('connexion') }}">Connexion</a>
<x-bouton :href="route('inscription')" taille="petit">Créer mon espace</x-bouton>
</nav></div></div></div>

<main>
<section class="vt vt-hero">
<div class="vt-fond" aria-hidden="true"><i></i><i></i></div>
<div>
<p class="vt-etiq" data-r>Port de Lomé · Transit et douane</p>
<h1>
<span class="ln"><span style="--i:0">Chaque dossier,</span></span>
<span class="ln"><span style="--i:1"><em>jalon après jalon</em>,</span></span>
<span class="ln"><span style="--i:2">jusqu’à la livraison.</span></span>
</h1>
<p class="vt-lead" data-r style="--d:5">Jalon aide les transitaires à piloter leurs dossiers d’import, à voir arriver la surestarie avant qu’elle coûte de l’argent, et à tenir leurs clients informés sans passer la journée au téléphone.</p>
<div class="vt-actions" data-r style="--d:6">
<x-bouton :href="route('inscription')">Je suis transitaire solo</x-bouton>
<x-bouton :href="route('inscription', ['vous' => 'agence'])" variante="encre">Je dirige une agence</x-bouton>
</div>
<form class="vt-suivi" method="GET" action="{{ route('suivi') }}" data-r style="--d:7">
<input type="text" name="code" placeholder="Code de suivi" aria-label="Code de suivi du dossier" autocomplete="off">
<button type="submit" class="btn btn-primaire">Suivre</button>
</form>
<p class="vt-aide" data-r style="--d:7">Importateur ? Entrez le code remis par votre transitaire, sans compte.</p>
</div>
<aside class="vt-carte-d" data-r style="--d:4" aria-label="Exemple de suivi d’un dossier">
<div class="vt-carte-d-tete"><strong>Suivi d’un dossier</strong><span class="vt-puce">Franchise : J-2</span></div>
<ol class="vt-jalons">
<li class="fait" style="--i:0">Dossier créé</li><li class="fait" style="--i:1">Documents transmis</li><li class="fait" style="--i:2">ECTN / BESC</li>
<li class="fait" style="--i:3">Manifeste déposé</li><li class="fait" style="--i:4">Déclaration soumise</li><li class="actuel">Inspection</li>
<li>Droits à payer</li><li>Bon à enlever</li><li>Marchandise livrée</li>
</ol>
<p class="vt-note">Exemple d’illustration.</p>
</aside>
</section>

<div class="vt-bande" aria-hidden="true"><div class="vt-piste">
@for ($k = 0; $k < 2; $k++)
<span>Documents</span><span>ECTN / BESC</span><span>Manifeste</span><span>Déclaration</span><span>Inspection</span><span>Droits</span><span>Bon à enlever</span><span>Franchise</span><span>Conteneurs</span><span>Facturation</span>
@endfor
</div></div>

<section class="vt vt-section" id="probleme">
<p class="vt-eyebrow" data-r>Le constat</p>
<h2 data-r>Aujourd’hui, le suivi se fait à la main.</h2>
<p class="vt-intro" data-r>Les outils officiels gèrent la procédure. Autour, il reste le pilotage de l’agence et la relation avec le client.</p>
<div class="vt-grille">
<article class="vt-c" data-r style="--d:0"><span class="vt-n">01</span><h3>Des infos éparpillées</h3><p>WhatsApp, appels, Excel, papier : documents perdus, étapes oubliées, personne ne sait où en est le dossier.</p></article>
<article class="vt-c" data-r style="--d:1"><span class="vt-n">02</span><h3>Une surestarie sans préavis</h3><p>Quand le délai gratuit au port est dépassé, chaque jour coûte. Trop souvent, l’échéance est vue trop tard.</p></article>
<article class="vt-c" data-r style="--d:2"><span class="vt-n">03</span><h3>Un client dans le flou</h3><p>L’importateur rappelle pour savoir où en est sa marchandise, et le transitaire répond au lieu d’avancer.</p></article>
</div>
</section>

<section class="vt vt-section" id="parcours">
<div class="pc">
<div class="pc-fixe">
<p class="vt-eyebrow">Le parcours</p>
<h2>Neuf jalons. Aucun oublié.</h2>
<p class="vt-intro" style="margin-bottom:0">Chaque passage d’étape est daté et conservé dans l’historique du dossier.</p>
<div class="pc-compteur"><span id="pcNum">01</span><small>/09</small></div>
<div class="pc-barre"><i id="pcBarre"></i></div>
</div>
<ol class="pc-liste" id="pcListe">
<li><span>01</span>Dossier créé</li><li><span>02</span>Documents transmis</li><li><span>03</span>ECTN / BESC</li>
<li><span>04</span>Manifeste déposé</li><li><span>05</span>Déclaration soumise</li><li><span>06</span>Inspection</li>
<li><span>07</span>Droits à payer</li><li><span>08</span>Bon à enlever</li><li><span>09</span>Marchandise livrée</li>
</ol>
</div>
</section>


<section class="vt vt-section" id="etapes" style="padding-top:0">
<p class="vt-eyebrow" data-r>Comment ça marche</p>
<h2 data-r>Du premier document à la livraison.</h2>
<div class="vt-grille" style="margin-top:32px">
<article class="vt-c" data-r style="--d:0"><span class="vt-n">1</span><h3>Vous créez le dossier</h3><p>Client, régime, références et premier conteneur. Un code de suivi est généré pour l’importateur.</p></article>
<article class="vt-c" data-r style="--d:1"><span class="vt-n">2</span><h3>Vous avancez jalon par jalon</h3><p>Documents, déclaration, inspection, droits, bon à enlever. Chaque étape est datée et ne peut pas être sautée.</p></article>
<article class="vt-c" data-r style="--d:2"><span class="vt-n">3</span><h3>Jalon vous prévient</h3><p>Alertes de franchise à J-2, J-1 et J0, documents manquants, factures impayées.</p></article>
<article class="vt-c" data-r style="--d:3"><span class="vt-n">4</span><h3>Le client suit sans appeler</h3><p>Avec son portail ou son code de suivi, il voit l’avancement et ses documents.</p></article>
</div>
</section>

@php
    $ecrans = [
        ['tableau-de-bord','Pilotage','Votre agence d’un coup d’œil','Chiffre d’affaires, volume de dossiers, marge, prochaines échéances et alertes importantes : l’essentiel de la journée sur un seul écran.',['Dossiers récents avec leur avancement sur 9 jalons','Calendrier des fins de franchise et des échéances','Journal d’activité de l’équipe']],
        ['fiche-dossier','Dossiers','Tout le dossier sur une seule fiche','Marge estimée et réelle, état de la facturation, documents validés, alerte de franchise et les 9 jalons du processus douanier.',['Passage au jalon suivant en un clic','Onglets Documents, Conteneurs, Coûts, Facture, Historique, Alertes']],
        ['passage-de-jalon-modale','Dossiers','Chaque étape validée, datée et commentée','Un jalon ne se franchit que si sa condition est remplie. La date et le commentaire restent dans l’historique du dossier.',['Condition de passage affichée avant de valider','Aucune étape sautée']],
        ['conteneurs','Conteneurs','Vos conteneurs et leurs échéances','Au port, sortis ou restitués : filtrez la liste et voyez d’un regard la fin de franchise et l’alerte associée.',['Alerte J0, J-1 ou J-2 sur chaque ligne','Recherche par numéro, dossier ou client']],
        ['alertes','Alertes','Prévenu avant que les pénalités commencent','Franchise, document manquant, facture à échéance, arrivée proche : toutes vos alertes, classées par gravité.',['Filtres Toutes, Non lues, Critiques','Ouvrir le dossier concerné ou marquer comme lue']],
        ['facturation','Facturation','Facturez et suivez ce qu’on vous doit','Les factures de chaque dossier avec leur état, le total, ce qui est déjà réglé et le reste à payer.',['États Émise, Partiellement payée, Payée','Marge du dossier sous les yeux','Version PDF de la facture']],
    ];
    $mobiles = [
        ['espace-client-mobile','Portail client','Tous ses dossiers, leur progression et leur alerte, en lecture seule.'],
        ['suivi-par-code-mobile','Suivi par code','L’importateur entre son code et voit où en est sa marchandise, sans compte.'],
    ];
@endphp
<section class="vt vt-section" id="apercu" style="padding-top:0">
<p class="vt-eyebrow" data-r>Aperçu</p>
<h2 data-r>L’interface, écran par écran.</h2>
<p class="vt-intro" data-r>Huit écrans, données fictives, captures filigranées. Cliquez sur une image pour l’agrandir.</p>
<div class="sh">
@foreach ($ecrans as $n => $e)
<article class="sh-l">
<div class="sh-f" data-r><div class="sh-b"><i></i><i></i><i></i></div>
<button type="button" class="sh-i" data-zoom="{{ asset('img/captures/'.$e[0].'.webp') }}" aria-label="Agrandir : {{ $e[2] }}"><img src="{{ asset('img/captures/'.$e[0].'.webp') }}" alt="{{ $e[2] }}" loading="lazy" decoding="async"></button></div>
<div class="sh-t" data-r style="--d:1"><p class="vt-eyebrow">{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }} · {{ $e[1] }}</p><h3>{{ $e[2] }}</h3><p>{{ $e[3] }}</p><ul>@foreach ($e[4] as $pt)<li>{{ $pt }}</li>@endforeach</ul></div>
</article>
@endforeach
</div>

<p class="vt-eyebrow" style="margin-top:72px" data-r>Pour vos clients</p>
<h2 data-r>Le suivi dans la poche de l’importateur.</h2>
<div class="ph" style="margin-top:28px">
@foreach ($mobiles as $m)
<figure data-r style="--d:{{ $loop->index }}"><button type="button" class="ph-i" data-zoom="{{ asset('img/captures/'.$m[0].'.webp') }}" aria-label="Agrandir : {{ $m[1] }}"><img src="{{ asset('img/captures/'.$m[0].'.webp') }}" alt="{{ $m[1] }}" loading="lazy" decoding="async"></button><figcaption><b>{{ $m[1] }}</b><br>{{ $m[2] }}</figcaption></figure>
@endforeach
</div>
</section>
<div class="lb" id="lb" hidden><img alt=""></div>

<section class="vt vt-section" id="fonctions" style="padding-top:0">
<p class="vt-eyebrow" data-r>Les fonctions</p>
<h2 data-r>Tout ce que Jalon fait aujourd’hui.</h2>
<p class="vt-intro" data-r>Jalon complète GUCE, SEGUCE et SYDONIA. Il ne les remplace pas : il gère ce qui reste autour de la procédure.</p>
<div class="vt-grille">
<article class="vt-c" data-r style="--d:0"><h3>Dossiers</h3><ul><li>Recherche par référence, client, B/L, code de suivi ou conteneur</li><li>9 jalons avec condition de passage</li><li>Fiche détaillée avec marge prévue puis réelle</li><li>Historique complet, archivage</li></ul></article>
<article class="vt-c" data-r style="--d:1"><h3>Conteneurs et franchise</h3><ul><li>Statut : au port, sorti, restitué</li><li>Calcul de la fin de franchise</li><li>Alertes J-2, J-1 et J0, chaque jour</li><li>Événements datés pour chaque changement</li></ul></article>
<article class="vt-c" data-r style="--d:2"><h3>Documents</h3><ul><li>Envoi PDF, JPG ou PNG, 5 Mo maximum</li><li>Pièces exigées selon le régime</li><li>Validation par l’équipe</li><li>Visibilité client au choix</li></ul></article>
<article class="vt-c" data-r style="--d:0"><h3>Coûts et facturation</h3><ul><li>Coûts saisis par dossier</li><li>Factures avec plusieurs lignes</li><li>Paiements partiels et solde restant</li><li>Version imprimable, à enregistrer en PDF</li></ul></article>
<article class="vt-c" data-r style="--d:1"><h3>Clients et portail</h3><ul><li>Répertoire avec historique et solde dû</li><li>Invitation à un portail en lecture seule</li><li>Suivi public par code, sans compte</li></ul></article>
<article class="vt-c" data-r style="--d:2"><h3>Équipe et permissions</h3><ul><li>Rôles : administrateur, transitaire, comptable</li><li>Invitation des employés par e-mail</li><li>Droits ajustables par personne</li></ul></article>
<article class="vt-c" data-r style="--d:0"><h3>Rapports et exports</h3><ul><li>Indicateurs par période</li><li>Journal d’activité</li><li>Exports CSV lisibles dans Excel</li></ul></article>
<article class="vt-c" data-r style="--d:1"><h3>Paramètres de l’agence</h3><ul><li>Identité et logo</li><li>Franchise, alertes, jalons, facturation</li><li>Sécurité et préférences</li></ul></article>
</div>
</section>

<section class="vt vt-section" id="securite" style="padding-top:0">
<p class="vt-eyebrow" data-r>Confiance</p>
<h2 data-r>Vos données restent les vôtres.</h2>
<div class="vt-grille" style="margin-top:28px">
<article class="vt-c" data-r style="--d:0"><h3>Documents privés</h3><p>Les fichiers sont dans un stockage non public. Chaque téléchargement passe par une vérification des droits.</p></article>
<article class="vt-c" data-r style="--d:1"><h3>Chaque agence isolée</h3><p>Une agence ne voit jamais les dossiers, clients ou factures d’une autre.</p></article>
<article class="vt-c" data-r style="--d:2"><h3>Accès contrôlés</h3><p>Mots de passe protégés, tentatives de connexion limitées, droits par rôle, journal d’activité.</p></article>
</div>
</section>

<section class="vt vt-section" id="pour-qui" style="padding-top:0">
<p class="vt-eyebrow" data-r>Pour qui</p>
<h2 data-r>Indépendants ou agences.</h2>
<p class="vt-intro" data-r>Deux façons de démarrer, selon la taille de votre structure.</p>
<div class="vt-grille">
<article class="vt-c vt-choix" data-r><h3>Transitaire solo</h3><p>Vous travaillez seul. Créez votre espace et gérez vos dossiers tout de suite.</p><x-bouton :href="route('inscription')">Créer mon espace</x-bouton></article>
<article class="vt-c vt-choix" data-r style="--d:1"><h3>Agence de transit</h3><p>Vous avez une équipe. Envoyez votre demande, nous vous contactons pour installer Jalon pour votre agence.</p><x-bouton :href="route('inscription', ['vous' => 'agence'])" variante="encre">Faire une demande</x-bouton></article>
</div>
</section>

<section class="vt vt-section" id="prevu" style="padding-top:0">
<p class="vt-eyebrow" data-r>Ce qui arrive</p>
<h2 data-r>Prévu, pas encore disponible.</h2>
<div class="pv" data-r style="margin-top:24px"><span>Bot WhatsApp de suivi</span><span>Notifications par SMS</span><span>Application mobile</span><span>Paiement en ligne</span><span>Module transporteur</span></div>
</section>

<section class="vt vt-section" id="faq" style="padding-top:0">
<p class="vt-eyebrow" data-r>Questions fréquentes</p>
<h2 data-r>Vous vous demandez peut-être…</h2>
<div class="fq" data-r style="margin-top:24px">
<details><summary>Jalon remplace-t-il GUCE ou SEGUCE ?</summary><p>Non. Ces outils restent nécessaires pour les démarches officielles. Jalon sert à piloter vos dossiers, vos équipes, vos coûts et la relation avec vos clients.</p></details>
<details><summary>Mon client a-t-il besoin d’un compte ?</summary><p>Non. Il peut suivre son dossier avec un simple code de suivi. Vous pouvez aussi lui ouvrir un portail en lecture seule.</p></details>
<details><summary>Quelle différence entre solo et agence ?</summary><p>Le transitaire solo crée son espace lui-même et travaille seul. L’agence envoie une demande, nous la contactons, puis Jalon est installé pour elle avec son équipe et ses rôles.</p></details>
<details><summary>Où sont stockés mes documents ?</summary><p>Dans un espace privé du serveur, jamais accessible par un lien public. L’accès est contrôlé selon vos droits.</p></details>
<details><summary>Quel est le prix ?</summary><p>Nous vous présentons l’offre lors de la prise de contact, selon la taille de votre structure.</p></details>
</div>
</section>

<section class="vt">
<div class="vt-final" data-r>
<h2>Prêt à voir où en sont vos dossiers ?</h2>
<p>Créez votre espace, ou connectez-vous si vous en avez déjà un.</p>
<div class="vt-actions"><x-bouton :href="route('inscription')">Commencer</x-bouton><x-bouton :href="route('connexion')" variante="encre">Connexion</x-bouton></div>
</div>
</section>
</main>

<footer class="vt vt-pied"><span>© Jalon, tous droits réservés</span><span><a href="{{ route('conditions') }}">Conditions d’utilisation</a> · <a href="{{ route('suivi') }}">Suivre un dossier</a></span></footer>

<script>
(function () {
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('vu'); io.unobserve(e.target); } });
  }, { threshold: .15 });
  document.querySelectorAll('[data-r]').forEach(function (el) { io.observe(el); });

  var barre = document.getElementById('barre');
  addEventListener('scroll', function () { barre.classList.toggle('defile', scrollY > 8); }, { passive: true });

  document.querySelectorAll('.vt-c').forEach(function (c) {
    c.addEventListener('pointermove', function (e) {
      var r = c.getBoundingClientRect();
      c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      c.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });



  var lb = document.getElementById('lb'), lbi = lb.querySelector('img');
  document.querySelectorAll('[data-zoom]').forEach(function (b) {
    b.addEventListener('click', function () { lbi.src = b.dataset.zoom; lb.hidden = false; lb.scrollTop = 0; document.body.style.overflow = 'hidden'; });
  });
  function fermer() { lb.hidden = true; document.body.style.overflow = ''; }
  lb.addEventListener('click', fermer);
  addEventListener('keydown', function (e) { if (e.key === 'Escape') fermer(); });

  var items = document.querySelectorAll('#pcListe li'), num = document.getElementById('pcNum'), bar = document.getElementById('pcBarre');
  var io2 = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      var i = Array.prototype.indexOf.call(items, e.target);
      items.forEach(function (li, k) { li.classList.toggle('actif', k <= i && k === i); });
      num.textContent = ('0' + (i + 1)).slice(-2);
      bar.style.width = ((i + 1) / items.length * 100) + '%';
    });
  }, { rootMargin: '-45% 0px -45% 0px' });
  items.forEach(function (li) { io2.observe(li); });
})();
</script>
</body>
</html>