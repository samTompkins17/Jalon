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
/* ==========================================================================
   Vitrine Jalon — mise en page éditoriale sur la charte de l'application.
   Néomorphisme très discret : uniquement --ombre-fine / relief / creux / pose.
   ========================================================================== */
html{scroll-behavior:smooth}
body{margin:0;background:var(--c-page);color:var(--c-ink);overflow-x:hidden;-webkit-font-smoothing:antialiased}
.vt{max-width:1180px;margin:0 auto;padding:0 24px}
.vt a{text-underline-offset:3px}
section[id]{scroll-margin-top:86px}
:root{--ease:cubic-bezier(.2,.7,.1,1);--vt-hair:1px solid var(--c-line)}

/* Animations d'entrée, sobres */
.js [data-r]{opacity:0;transform:translateY(18px);transition:opacity .8s var(--ease),transform .8s var(--ease);transition-delay:calc(var(--d,0)*80ms)}
.js [data-r].vu{opacity:1;transform:none}
@keyframes monte{from{transform:translateY(105%)}to{transform:none}}
@keyframes fondu{from{opacity:0}to{opacity:1}}
@keyframes derive{0%,100%{transform:translate3d(0,0,0)}50%{transform:translate3d(-26px,18px,0)}}
@keyframes defile{to{transform:translateX(-50%)}}
.ln{display:block;overflow:hidden;padding-bottom:.06em}
.ln>span{display:block;transform:translateY(105%);animation:monte 1s var(--ease) forwards;animation-delay:calc(var(--i,0)*110ms + 120ms)}
@media (prefers-reduced-motion:reduce){
  *,*::before,*::after{animation:none!important;transition:none!important}
  .js [data-r]{opacity:1;transform:none}.ln>span{transform:none}.vt-piste{width:auto;flex-wrap:wrap;animation:none}
}

/* ---------- Barre de navigation ---------- */
.vt-barre{position:sticky;top:0;z-index:30;background:color-mix(in srgb,var(--c-page) 86%,transparent);backdrop-filter:blur(16px);border-bottom:1px solid transparent;transition:border-color .3s,background .3s}
.vt-barre.defile{border-bottom-color:var(--c-line);background:color-mix(in srgb,var(--c-page) 94%,transparent)}
.vt-nav{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:28px;padding:15px 0}
.vt-liens{display:flex;align-items:center;justify-content:center;gap:30px}
.vt-liens a{position:relative;color:var(--c-ink-2);text-decoration:none;font-size:.94rem;font-weight:500;padding:4px 0}
.vt-liens a::after{content:"";position:absolute;left:0;right:0;bottom:-2px;height:1px;background:var(--c-brand);transform:scaleX(0);transform-origin:left;transition:transform .3s var(--ease)}
.vt-liens a:hover{color:var(--c-ink)}
.vt-liens a:hover::after{transform:scaleX(1)}
.vt-nav-actions{display:flex;align-items:center;gap:16px}
.vt-nav-actions a:not(.btn){color:var(--c-ink-2);text-decoration:none;font-size:.94rem;font-weight:500}
.vt-nav-actions a:not(.btn):hover{color:var(--c-brand)}

/* ---------- Héro ---------- */
.vt-hero{position:relative;display:grid;grid-template-columns:1.04fr .96fr;gap:64px;align-items:center;padding:76px 0 84px}
.vt-halo{position:absolute;inset:-140px -8vw auto;height:620px;z-index:-1;pointer-events:none;overflow:hidden}
.vt-halo i{position:absolute;display:block;border-radius:50%;filter:blur(90px);opacity:.55}
.vt-halo i:first-child{width:520px;height:420px;right:4%;top:24px;background:color-mix(in srgb,var(--c-brand) 16%,transparent);animation:derive 22s ease-in-out infinite}
.vt-halo i:last-child{width:380px;height:340px;left:2%;top:180px;background:color-mix(in srgb,var(--c-teal) 9%,transparent);animation:derive 26s ease-in-out infinite reverse}
.vt-eyebrow{display:flex;align-items:center;gap:10px;margin:0 0 22px;color:var(--c-muted);font-size:.72rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase}
.vt-eyebrow::before{content:"";width:20px;height:1px;background:var(--c-brand)}
.vt h1{font-size:clamp(2.6rem,5.4vw,4.9rem);line-height:1;letter-spacing:-.045em;margin:0 0 24px;font-weight:700}
.vt h1 em{font-style:normal;color:var(--c-brand)}
.vt-lead{font-size:1.14rem;line-height:1.62;color:var(--c-ink-2);max-width:46ch;margin:0 0 30px}
.vt-actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:30px}
.vt-suivi{display:flex;gap:8px;max-width:440px}
.vt-suivi input{flex:1;min-width:0;padding:13px 16px;border:1px solid var(--c-line);border-radius:12px;background:var(--c-surface);color:var(--c-ink);font:inherit;font-size:.95rem;text-transform:uppercase;letter-spacing:.04em;box-shadow:var(--ombre-creux)}
.vt-suivi input::placeholder{text-transform:none;letter-spacing:0;color:var(--c-muted)}
.vt-suivi input:focus-visible{outline:none;border-color:var(--c-brand);box-shadow:0 0 0 3px var(--c-brand-soft),var(--ombre-creux)}
.vt-aide{font-size:.86rem;color:var(--c-muted);margin:12px 0 0;max-width:44ch}

/* Carte de démonstration */
.vt-carte{position:relative;background:var(--c-surface);border:1px solid var(--c-line);border-radius:22px;padding:24px 24px 20px;box-shadow:var(--ombre-pose)}
.vt-carte-tete{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding-bottom:16px;border-bottom:var(--vt-hair)}
.vt-carte-tete small{display:block;font-size:.68rem;letter-spacing:.16em;text-transform:uppercase;color:var(--c-muted);margin-bottom:6px}
.vt-carte-tete strong{font-size:1.02rem;letter-spacing:-.01em}
.vt-puce{display:inline-flex;align-items:center;gap:7px;padding:5px 11px;border-radius:999px;background:var(--c-rose-bg);color:var(--c-rose);font-size:.74rem;font-weight:600;white-space:nowrap}
.vt-puce::before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}
.vt-jalons{list-style:none;margin:16px 0 0;padding:0}
.vt-jalons li{position:relative;display:flex;align-items:center;gap:13px;padding:8px 0;font-size:.9rem;color:var(--c-muted);transition:color .4s}
.vt-jalons li::before{content:"";flex:none;width:13px;height:13px;border-radius:50%;border:1.5px solid var(--c-line-fort);background:var(--c-surface)}
.vt-jalons li.fait{color:var(--c-ink-2)}
.vt-jalons li.fait::before{background:var(--c-brand);border-color:var(--c-brand);box-shadow:inset 0 0 0 2px var(--c-surface)}
.vt-jalons li.actuel{color:var(--c-ink);font-weight:600}
.vt-jalons li.actuel::before{border-color:var(--c-brand);box-shadow:0 0 0 4px var(--c-brand-soft)}
.vt-carte-pied{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding-top:15px;border-top:var(--vt-hair);font-size:.8rem;color:var(--c-muted)}
.vt-jauge{flex:1;height:3px;border-radius:3px;background:var(--c-surface-2);box-shadow:var(--ombre-creux);overflow:hidden}
.vt-jauge i{display:block;height:100%;width:55%;background:var(--c-brand);border-radius:3px}
.vt-note{font-size:.76rem;color:var(--c-muted);margin:12px 0 0}

/* ---------- Bandeau des modules ---------- */
.vt-bande{border-block:1px solid var(--c-line);overflow:hidden;padding:14px 0;-webkit-mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent);mask-image:linear-gradient(90deg,transparent,#000 10%,#000 90%,transparent)}
.vt-piste{display:flex;gap:38px;width:max-content;animation:defile 46s linear infinite;color:var(--c-muted);font-size:.72rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;white-space:nowrap}
.vt-piste span{display:flex;align-items:center;gap:38px}
.vt-piste span::after{content:"";width:3px;height:3px;border-radius:50%;background:var(--c-line-fort)}
.vt-bande:hover .vt-piste{animation-play-state:paused}

/* ---------- Sections ---------- */
.vt-section{padding:92px 0;border-top:var(--vt-hair)}
.vt-section--sans{ border-top:0}
.vt-entete{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:end;margin-bottom:40px}
.vt-entete h2{font-size:clamp(1.9rem,3.4vw,2.9rem);line-height:1.06;letter-spacing:-.035em;margin:0 0 14px;max-width:22ch}
.vt-entete .vt-intro{margin:0}
.vt-intro{color:var(--c-muted);max-width:58ch;font-size:1.02rem;line-height:1.64}
.vt-index{font-size:.72rem;letter-spacing:.16em;color:var(--c-muted);white-space:nowrap;padding-bottom:6px}
.vt-grille{display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:16px}

/* Cartes */
.vt-c{position:relative;background:var(--c-surface);border:1px solid var(--c-line);border-radius:16px;padding:26px;box-shadow:var(--ombre-fine);transition:box-shadow .3s,transform .4s var(--ease)}
.vt-c:hover{box-shadow:var(--ombre-relief);transform:translateY(-2px)}
.vt-c h3{margin:0 0 8px;font-size:1.06rem;letter-spacing:-.015em}
.vt-c p{margin:0;color:var(--c-ink-2);line-height:1.58;font-size:.95rem}
.vt-n{display:block;font-size:.7rem;font-weight:600;letter-spacing:.16em;color:var(--c-brand);margin-bottom:16px}

/* Parcours */
.pc{display:grid;grid-template-columns:.86fr 1.14fr;gap:60px;align-items:start}
.pc-fixe{position:sticky;top:112px}
.pc-compteur{font-size:clamp(3.4rem,8vw,6rem);font-weight:700;letter-spacing:-.06em;line-height:1;margin:22px 0 16px;color:var(--c-brand);font-variant-numeric:tabular-nums}
.pc-compteur small{font-size:.22em;font-weight:600;color:var(--c-muted);letter-spacing:0}
.pc-barre{height:2px;border-radius:2px;background:var(--c-line);overflow:hidden;max-width:300px}
.pc-barre i{display:block;height:100%;width:11%;background:var(--c-brand);transition:width .5s var(--ease)}
.pc-liste{list-style:none;margin:0;padding:0}
.pc-liste li{display:flex;align-items:center;gap:20px;padding:22px 0;border-top:var(--vt-hair);font-size:clamp(1.15rem,2.2vw,1.5rem);font-weight:600;letter-spacing:-.02em;color:var(--c-muted);transition:color .45s,transform .5s var(--ease)}
.pc-liste li:last-child{border-bottom:var(--vt-hair)}
.pc-liste li span{font-size:.68rem;font-weight:600;letter-spacing:.12em;color:var(--c-muted);width:3em;font-variant-numeric:tabular-nums}
.pc-liste li.actif{color:var(--c-ink);transform:translateX(8px)}
.pc-liste li.actif span{color:var(--c-brand)}

/* Étapes */
.etapes{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin-top:12px;border-top:var(--vt-hair)}
.etapes article{position:relative;padding:28px 24px 0 0;border-right:var(--vt-hair)}
.etapes article:last-child{border-right:0;padding-right:0}
.etapes article::before{content:"";position:absolute;top:-3px;left:0;width:5px;height:5px;border-radius:50%;background:var(--c-brand)}
.etapes .num{display:block;font-size:.7rem;letter-spacing:.16em;color:var(--c-muted);margin-bottom:12px}
.etapes h3{margin:0 0 8px;font-size:1rem;letter-spacing:-.01em}
.etapes p{margin:0;color:var(--c-ink-2);font-size:.92rem;line-height:1.58}

/* Galerie */
.sh{display:grid;gap:76px;margin-top:8px}
.sh-l{display:grid;grid-template-columns:1.3fr .7fr;gap:48px;align-items:center}
.sh-l:nth-child(even){grid-template-columns:.7fr 1.3fr}
.sh-l:nth-child(even) .sh-f{order:2}
.sh-f{border:1px solid var(--c-line);border-radius:18px;background:var(--c-surface);overflow:hidden;box-shadow:var(--ombre-pose)}
.sh-b{display:flex;gap:6px;padding:11px 14px;border-bottom:var(--vt-hair);background:var(--c-surface-2)}
.sh-b i{width:8px;height:8px;border-radius:50%;background:var(--c-line-fort)}
.sh-i,.ph-i{display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in}
.sh-i{height:min(44vw,440px);overflow:hidden}
.sh-i img{width:100%;height:100%;object-fit:cover;object-position:top;display:block;transition:transform 1.4s var(--ease)}
.sh-f:hover .sh-i img{transform:scale(1.02)}
.sh-t h3{font-size:clamp(1.32rem,2.3vw,1.85rem);letter-spacing:-.028em;line-height:1.14;margin:0 0 12px}
.sh-t p{color:var(--c-ink-2);line-height:1.6;margin:0;font-size:.98rem}
.sh-t ul{margin:16px 0 0;padding:0;list-style:none;color:var(--c-muted);font-size:.9rem;line-height:1.6}
.sh-t li{display:flex;gap:10px;padding:5px 0}
.sh-t li::before{content:"";flex:none;width:5px;height:5px;margin-top:.55em;border-radius:50%;background:var(--c-brand);opacity:.65}
.ph{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:32px;align-items:start;margin-top:28px}
.ph figure{margin:0;text-align:center}
.ph-i{border:1px solid var(--c-line);border-radius:26px;overflow:hidden;box-shadow:var(--ombre-pose);max-width:250px;margin:0 auto 16px;background:var(--c-surface)}
.ph-i img{display:block;width:100%;height:auto}
.ph figcaption{color:var(--c-muted);font-size:.88rem;line-height:1.55;max-width:30ch;margin:0 auto}
.ph figcaption b{display:block;color:var(--c-ink);font-size:.95rem;margin-bottom:3px}
.lb{position:fixed;inset:0;z-index:60;background:color-mix(in srgb,#000 84%,transparent);overflow:auto;padding:32px;cursor:zoom-out;animation:fondu .25s}
.lb[hidden]{display:none}
.lb img{display:block;max-width:min(1240px,100%);margin:0 auto;border-radius:12px;height:auto;box-shadow:var(--ombre-pose)}

/* Fonctions */
.fn{display:grid;grid-template-columns:repeat(2,1fr);column-gap:52px}
.fn article{padding:24px 0 22px;border-top:var(--vt-hair)}
.fn h3{margin:0 0 8px;font-size:1rem;letter-spacing:-.01em;display:flex;align-items:baseline;gap:10px}
.fn h3 span{font-size:.68rem;font-weight:600;letter-spacing:.14em;color:var(--c-brand)}
.fn p{display:none}
.fn ul{margin:0;padding:0;list-style:none;color:var(--c-ink-2);font-size:.91rem;line-height:1.62}
.fn li{position:relative;padding-left:14px}
.fn li::before{content:"";position:absolute;left:0;top:.62em;width:4px;height:4px;border-radius:50%;background:var(--c-line-fort)}

/* Confiance, pour qui, prévu */
.vt-trois{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;border-top:var(--vt-hair);padding-top:32px}
.vt-trois h3{margin:0 0 10px;font-size:1.02rem}
.vt-trois p{margin:0;color:var(--c-ink-2);font-size:.94rem;line-height:1.6}
.vt-choix{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.vt-choix article{display:flex;flex-direction:column;align-items:flex-start;gap:14px;padding:30px}
.vt-choix article p{flex:1}
.vt-choix article:last-child{background:color-mix(in srgb,var(--c-brand-soft) 45%,var(--c-surface));border-color:color-mix(in srgb,var(--c-brand) 18%,var(--c-line))}
.pv{display:flex;flex-wrap:wrap;gap:10px}
.pv span{padding:9px 15px;border:1px dashed var(--c-line-fort);border-radius:999px;color:var(--c-ink-2);font-size:.88rem;font-weight:500;background:color-mix(in srgb,var(--c-surface) 60%,transparent)}

/* FAQ */
.fq details{border-top:var(--vt-hair);padding:20px 0}
.fq details:last-of-type{border-bottom:var(--vt-hair)}
.fq summary{cursor:pointer;font-weight:600;font-size:1rem;list-style:none;display:flex;justify-content:space-between;gap:20px;align-items:center}
.fq summary::-webkit-details-marker{display:none}
.fq summary::after{content:"+";color:var(--c-brand);font-size:1.3rem;line-height:1;transition:transform .3s}
.fq details[open] summary::after{transform:rotate(45deg)}
.fq p{color:var(--c-ink-2);line-height:1.62;max-width:74ch;margin:14px 0 0;font-size:.95rem}

/* Appel final */
.vt-final{position:relative;overflow:hidden;margin:0 auto 8px;padding:72px 40px;text-align:center;border-radius:26px;background:var(--c-ink);color:var(--c-page);box-shadow:var(--ombre-pose)}
.vt-final::before{content:"";position:absolute;inset:-40% -10% auto;height:520px;background:radial-gradient(50% 60% at 50% 0%,color-mix(in srgb,var(--c-brand) 46%,transparent),transparent 72%);opacity:.5;pointer-events:none}
.vt-final h2{position:relative;margin:0 auto 14px;max-width:19ch;color:var(--c-page);font-size:clamp(1.8rem,3.6vw,2.7rem);letter-spacing:-.035em;line-height:1.08}
.vt-final p{position:relative;margin:0 auto 28px;max-width:46ch;opacity:.72}
.vt-final .vt-actions{position:relative;justify-content:center;margin:0}

/* ---------- Pied de page ---------- */
.vt-pied{border-top:var(--vt-hair);margin-top:64px;background:color-mix(in srgb,var(--c-surface-2) 55%,var(--c-page))}
.vt-pied-haut{display:grid;grid-template-columns:1.5fr repeat(3,.85fr);gap:40px;padding:56px 0 44px}
.vt-marque p{margin:16px 0 0;color:var(--c-ink-2);font-size:.92rem;line-height:1.6;max-width:32ch}
.vt-marque .lieu{display:inline-flex;align-items:center;gap:8px;margin-top:18px;color:var(--c-muted);font-size:.8rem;letter-spacing:.02em}
.vt-marque .lieu::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--c-brand)}
.vt-col h4{margin:0 0 16px;font-size:.7rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--c-muted)}
.vt-col ul{list-style:none;margin:0;padding:0}
.vt-col li{margin:0 0 10px}
.vt-col a{color:var(--c-ink-2);text-decoration:none;font-size:.9rem}
.vt-col a:hover{color:var(--c-brand)}
.vt-pied-bas{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:18px 0 22px;border-top:var(--vt-hair);color:var(--c-muted);font-size:.82rem}
.vt-pied-bas a{color:var(--c-muted);text-decoration:none}
.vt-pied-bas a:hover{color:var(--c-brand)}
.vt-pied-bas .droits{color:var(--c-ink-2);font-weight:500}

/* ---------- Responsive ---------- */
@media (max-width:980px){
  .vt-hero{grid-template-columns:1fr;gap:36px;padding:48px 0 64px}
  .vt-nav{grid-template-columns:1fr auto;gap:14px}
  .vt-liens{display:none}
  .pc{grid-template-columns:1fr;gap:24px}
  .pc-fixe{position:static}
  .pc-liste li{padding:16px 0}
  .etapes{grid-template-columns:repeat(2,1fr)}
  .etapes article{padding:24px 22px 26px 0;border-bottom:var(--vt-hair)}
  .etapes article:nth-child(2n){border-right:0;padding-right:0}
  .sh-l,.sh-l:nth-child(even){grid-template-columns:1fr;gap:22px}
  .sh-l:nth-child(even) .sh-f{order:0}
  .sh{gap:52px}
  .sh-i{height:250px}
  .sh-t ul{margin-top:12px}
  .vt-entete{grid-template-columns:1fr}
  .vt-index{display:none}
  .fn{grid-template-columns:1fr;column-gap:0}
  .vt-trois{grid-template-columns:1fr;gap:22px}
  .vt-choix{grid-template-columns:1fr}
  .vt-pied-haut{grid-template-columns:1fr 1fr;gap:32px;padding:40px 0 32px}
  .vt-marque{grid-column:1/-1}
  .vt-section{padding:56px 0}
  .vt-final{padding:52px 24px}
}
@media (max-width:560px){
  .vt-pied-haut{grid-template-columns:1fr}
  .vt-nav-actions{gap:12px}
  .vt-nav-actions a:not(.btn){font-size:.88rem}
}
</style>
</head>
<body id="haut">
<div class="vt-barre" id="barre"><div class="vt"><div class="vt-nav">
<a href="{{ url('/') }}" aria-label="Jalon, accueil"><x-logo :hauteur="28" /></a>
<nav class="vt-liens" aria-label="Navigation principale">
<a href="#parcours">Parcours</a>
<a href="#apercu">Aperçu</a>
<a href="#fonctions">Fonctions</a>
<a href="#pour-qui">Pour qui</a>
<a href="#faq">FAQ</a>
</nav>
<div class="vt-nav-actions">
<a href="{{ route('connexion') }}">Connexion</a>
<x-bouton :href="route('inscription')" taille="petit">Créer mon espace</x-bouton>
</div>
</div></div></div>

<main>
<section class="vt vt-hero">
<div class="vt-halo" aria-hidden="true"><i></i><i></i></div>

<div>
<p class="vt-eyebrow" data-r>Port de Lomé · Transit et douane</p>
<h1>
<span class="ln"><span style="--i:0">Chaque dossier,</span></span>
<span class="ln"><span style="--i:1"><em>jalon après jalon</em>,</span></span>
<span class="ln"><span style="--i:2">jusqu’à la livraison.</span></span>
</h1>
<p class="vt-lead" data-r style="--d:4">Jalon réunit vos dossiers d’import, vos conteneurs, votre facturation et vos clients dans une seule interface, et donne à chaque importateur un suivi clair, sans appel.</p>
<div class="vt-actions" data-r style="--d:5">
<x-bouton :href="route('inscription')">Je suis transitaire solo</x-bouton>
<x-bouton :href="route('inscription', ['vous' => 'agence'])" variante="encre">Je dirige une agence</x-bouton>
</div>
<form class="vt-suivi" method="GET" action="{{ route('suivi') }}" data-r style="--d:6">
<input type="text" name="code" placeholder="Code de suivi" aria-label="Code de suivi du dossier" autocomplete="off">
<button type="submit" class="btn btn-primaire">Suivre</button>
</form>
<p class="vt-aide" data-r style="--d:6">Importateur ? Entrez le code remis par votre transitaire, sans compte.</p>
</div>

<aside class="vt-carte" data-r style="--d:3" aria-label="Exemple de suivi d’un dossier">
<div class="vt-carte-tete">
<div><small>Dossier JL-2027-0031</small><strong>Société Adjo Import</strong></div>
<span class="vt-puce">Franchise : J-2</span>
</div>
<ol class="vt-jalons">
<li class="fait">Dossier créé</li>
<li class="fait">Documents transmis</li>
<li class="fait">ECTN / BESC</li>
<li class="fait">Manifeste déposé</li>
<li class="fait">Déclaration soumise</li>
<li class="actuel">Inspection</li>
<li>Droits à payer</li>
<li>Bon à enlever</li>
<li>Marchandise livrée</li>
</ol>
<div class="vt-carte-pied"><span>Étape 6 sur 9</span><span class="vt-jauge" aria-hidden="true"><i></i></span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</aside>
</section>

<div class="vt-bande" aria-hidden="true"><div class="vt-piste">
@for ($k = 0; $k < 2; $k++)
<span>Documents</span><span>ECTN / BESC</span><span>Manifeste</span><span>Déclaration</span><span>Inspection</span><span>Droits</span><span>Bon à enlever</span><span>Franchise</span><span>Conteneurs</span><span>Facturation</span>
@endfor
</div></div>

<section class="vt vt-section" id="solution">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>La solution</p>
<h2 data-r>Un seul endroit pour piloter chaque dossier.</h2>
<p class="vt-intro" data-r>Jalon réunit le pilotage de l’agence et la relation client dans une interface unique, pensée pour le transit au Port de Lomé.</p>
</div>
<span class="vt-index">01 / 07</span>
</div>
<div class="vt-grille">
<article class="vt-c" data-r><span class="vt-n">Pilotage</span><h3>Tout au même endroit</h3><p>Dossiers, pièces justificatives, conteneurs, coûts et factures vivent dans la même fiche, à jour à chaque étape.</p></article>
<article class="vt-c" data-r style="--d:1"><span class="vt-n">Franchise</span><h3>La date limite toujours connue</h3><p>La fin de franchise est calculée pour chaque conteneur et rappelée à J-2, J-1 et J0, dans l’application.</p></article>
<article class="vt-c" data-r style="--d:2"><span class="vt-n">Relation client</span><h3>Le client suit tout seul</h3><p>Un code de suivi ou un portail en lecture seule : l’importateur voit l’avancement et ses documents, sans appeler.</p></article>
</div>
</section>

<section class="vt vt-section" id="parcours">
<div class="pc">
<div class="pc-fixe">
<p class="vt-eyebrow" data-r>Le parcours</p>
<h2 data-r style="font-size:clamp(1.9rem,3.4vw,2.8rem);letter-spacing:-.035em;line-height:1.06;margin:0 0 14px">Neuf jalons. Aucun oublié.</h2>
<p class="vt-intro" style="margin-bottom:0">Chaque passage d’étape est daté et conservé dans l’historique du dossier.</p>
<div class="pc-compteur"><span id="pcNum">01</span><small>/09</small></div>
<div class="pc-barre"><i id="pcBarre"></i></div>
</div>
<ol class="pc-liste" id="pcListe">
<li><span>01</span>Dossier créé</li>
<li><span>02</span>Documents transmis</li>
<li><span>03</span>ECTN / BESC</li>
<li><span>04</span>Manifeste déposé</li>
<li><span>05</span>Déclaration soumise</li>
<li><span>06</span>Inspection</li>
<li><span>07</span>Droits à payer</li>
<li><span>08</span>Bon à enlever</li>
<li><span>09</span>Marchandise livrée</li>
</ol>
</div>
</section>

<section class="vt vt-section" id="etapes">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Comment ça marche</p>
<h2 data-r>Du premier document à la livraison.</h2>
</div>
<span class="vt-index">02 / 07</span>
</div>
<div class="etapes">
<article data-r><span class="num">1</span><h3>Vous créez le dossier</h3><p>Client, régime, références et premier conteneur. Un code de suivi est généré pour l’importateur.</p></article>
<article data-r style="--d:1"><span class="num">2</span><h3>Vous avancez jalon par jalon</h3><p>Documents, déclaration, inspection, droits, bon à enlever. Chaque étape est datée et ne peut pas être sautée.</p></article>
<article data-r style="--d:2"><span class="num">3</span><h3>Jalon vous prévient</h3><p>Alertes de franchise à J-2, J-1 et J0, documents manquants, factures impayées.</p></article>
<article data-r style="--d:3"><span class="num">4</span><h3>Le client suit sans appeler</h3><p>Avec son portail ou son code de suivi, il voit l’avancement et ses documents.</p></article>
</div>
</section>

@php
    $ecrans = [
        ['tableau-de-bord','Pilotage','Votre agence d’un coup d’œil','Chiffre d’affaires, volume de dossiers, marge, prochaines échéances et alertes importantes : l’essentiel de la journée sur un seul écran.',['Dossiers récents avec leur avancement sur 9 jalons','Calendrier des fins de franchise et des échéances','Journal d’activité de l’équipe']],
        ['fiche-dossier','Dossiers','Tout le dossier sur une seule fiche','Marge estimée et réelle, état de la facturation, documents validés, alerte de franchise et les 9 jalons du processus douanier.',['Passage au jalon suivant en un clic','Onglets Documents, Conteneurs, Coûts, Facture, Historique, Alertes']],
        ['passage-de-jalon-modale','Dossiers','Chaque étape validée, datée et commentée','Un jalon ne se franchit que si sa condition est remplie. La date et le commentaire restent dans l’historique du dossier.',['Condition de passage affichée avant de valider','Aucune étape sautée']],
        ['conteneurs','Conteneurs','Vos conteneurs et leurs échéances','Au port, sortis ou restitués : filtrez la liste et voyez d’un regard la fin de franchise et l’alerte associée.',['Alerte J0, J-1 ou J-2 sur chaque ligne','Recherche par numéro, dossier ou client']],
        ['alertes','Alertes','Chaque échéance annoncée à l’avance','Franchise, document à fournir, facture à échéance, arrivée proche : toutes vos alertes, classées par priorité.',['Filtres Toutes, Non lues, Critiques','Ouvrir le dossier concerné ou marquer comme lue']],
        ['facturation','Facturation','Facturez et suivez chaque règlement','Les factures de chaque dossier avec leur état, le total, les acomptes reçus et le solde à venir.',['États Émise, Partiellement payée, Payée','Marge du dossier sous les yeux','Version PDF de la facture']],
    ];
    $mobiles = [
        ['espace-client-mobile','Portail client','Tous ses dossiers, leur progression et leur alerte, en lecture seule.'],
        ['suivi-par-code-mobile','Suivi par code','L’importateur entre son code et voit où en est sa marchandise, sans compte.'],
    ];
@endphp
<section class="vt vt-section" id="apercu">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Aperçu</p>
<h2 data-r>L’interface, écran par écran.</h2>
<p class="vt-intro" data-r>Huit écrans, données fictives, captures filigranées. Cliquez sur une image pour l’agrandir.</p>
</div>
<span class="vt-index">03 / 07</span>
</div>
<div class="sh">
@foreach ($ecrans as $n => $e)
<article class="sh-l">
<div class="sh-f" data-r><div class="sh-b"><i></i><i></i><i></i></div>
<button type="button" class="sh-i" data-zoom="{{ asset('img/captures/'.$e[0].'.webp') }}" aria-label="Agrandir : {{ $e[2] }}"><img src="{{ asset('img/captures/'.$e[0].'.webp') }}" alt="{{ $e[2] }}" loading="lazy" decoding="async"></button></div>
<div class="sh-t" data-r style="--d:1">
<p class="vt-eyebrow">{{ str_pad($n + 1, 2, '0', STR_PAD_LEFT) }} · {{ $e[1] }}</p>
<h3>{{ $e[2] }}</h3><p>{{ $e[3] }}</p>
<ul>@foreach ($e[4] as $pt)<li>{{ $pt }}</li>@endforeach</ul>
</div>
</article>
@endforeach
</div>

<div style="margin-top:76px">
<div class="vt-entete">
<div><p class="vt-eyebrow" data-r>Pour vos clients</p>
<h2 data-r>Le suivi dans la poche de l’importateur.</h2></div>
</div>
<div class="ph">
@foreach ($mobiles as $m)
<figure data-r style="--d:{{ $loop->index }}"><button type="button" class="ph-i" data-zoom="{{ asset('img/captures/'.$m[0].'.webp') }}" aria-label="Agrandir : {{ $m[1] }}"><img src="{{ asset('img/captures/'.$m[0].'.webp') }}" alt="{{ $m[1] }}" loading="lazy" decoding="async"></button><figcaption><b>{{ $m[1] }}</b>{{ $m[2] }}</figcaption></figure>
@endforeach
</div>
</div>
</section>
<div class="lb" id="lb" hidden><img alt=""></div>

<section class="vt vt-section" id="fonctions">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Les fonctions</p>
<h2 data-r>Tout ce que Jalon fait aujourd’hui.</h2>
<p class="vt-intro" data-r>Jalon complète GUCE, SEGUCE et SYDONIA. Il ne les remplace pas : il gère ce qui reste autour de la procédure.</p>
</div>
<span class="vt-index">04 / 07</span>
</div>
<div class="fn">
<article data-r><h3><span>01</span>Dossiers</h3><ul><li>Recherche par référence, client, B/L, code de suivi ou conteneur</li><li>9 jalons avec condition de passage</li><li>Fiche détaillée avec marge prévue puis réelle</li><li>Historique complet, archivage</li></ul></article>
<article data-r style="--d:1"><h3><span>02</span>Conteneurs et franchise</h3><ul><li>Statut : au port, sorti, restitué</li><li>Calcul de la fin de franchise</li><li>Alertes J-2, J-1 et J0, chaque jour</li><li>Événements datés pour chaque changement</li></ul></article>
<article data-r><h3><span>03</span>Documents</h3><ul><li>Envoi PDF, JPG ou PNG, 5 Mo maximum</li><li>Pièces exigées selon le régime</li><li>Validation par l’équipe</li><li>Visibilité client au choix</li></ul></article>
<article data-r style="--d:1"><h3><span>04</span>Coûts et facturation</h3><ul><li>Coûts saisis par dossier</li><li>Factures avec plusieurs lignes</li><li>Paiements partiels et solde restant</li><li>Version imprimable, à enregistrer en PDF</li></ul></article>
<article data-r><h3><span>05</span>Clients et portail</h3><ul><li>Répertoire avec historique et solde dû</li><li>Invitation à un portail en lecture seule</li><li>Suivi public par code, sans compte</li></ul></article>
<article data-r style="--d:1"><h3><span>06</span>Équipe et permissions</h3><ul><li>Rôles : administrateur, transitaire, comptable</li><li>Invitation des employés par e-mail</li><li>Droits ajustables par personne</li></ul></article>
<article data-r><h3><span>07</span>Rapports et exports</h3><ul><li>Indicateurs par période</li><li>Journal d’activité</li><li>Exports CSV lisibles dans Excel</li></ul></article>
<article data-r style="--d:1"><h3><span>08</span>Paramètres de l’agence</h3><ul><li>Identité et logo</li><li>Franchise, alertes, jalons, facturation</li><li>Sécurité et préférences</li></ul></article>
</div>
</section>

<section class="vt vt-section" id="securite">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Confiance</p>
<h2 data-r>Vos données restent les vôtres.</h2>
</div>
<span class="vt-index">05 / 07</span>
</div>
<div class="vt-trois">
<div data-r><h3>Documents privés</h3><p>Les fichiers sont dans un stockage non public. Chaque téléchargement passe par une vérification des droits.</p></div>
<div data-r style="--d:1"><h3>Chaque agence isolée</h3><p>Une agence ne voit jamais les dossiers, clients ou factures d’une autre.</p></div>
<div data-r style="--d:2"><h3>Accès contrôlés</h3><p>Mots de passe protégés, tentatives de connexion limitées, droits par rôle, journal d’activité.</p></div>
</div>
</section>

<section class="vt vt-section" id="pour-qui">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Pour qui</p>
<h2 data-r>Indépendants ou agences.</h2>
<p class="vt-intro" data-r>Deux façons de démarrer, selon la taille de votre structure.</p>
</div>
<span class="vt-index">06 / 07</span>
</div>
<div class="vt-choix">
<article class="vt-c" data-r><h3>Transitaire solo</h3><p>Vous travaillez seul. Créez votre espace et gérez vos dossiers tout de suite.</p><x-bouton :href="route('inscription')">Créer mon espace</x-bouton></article>
<article class="vt-c" data-r style="--d:1"><h3>Agence de transit</h3><p>Vous avez une équipe. Envoyez votre demande, nous vous contactons pour installer Jalon pour votre agence.</p><x-bouton :href="route('inscription', ['vous' => 'agence'])" variante="encre">Faire une demande</x-bouton></article>
</div>
</section>

<section class="vt vt-section" id="prevu">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Ce qui arrive</p>
<h2 data-r>Prévu, pas encore disponible.</h2>
</div>
<span class="vt-index">07 / 07</span>
</div>
<div class="pv" data-r><span>Bot WhatsApp de suivi</span><span>Notifications par SMS</span><span>Application mobile</span><span>Paiement en ligne</span><span>Module transporteur</span></div>
</section>

<section class="vt vt-section" id="faq">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Questions fréquentes</p>
<h2 data-r>Vous vous demandez peut-être…</h2>
</div>
</div>
<div class="fq" data-r>
<details><summary>Jalon remplace-t-il GUCE ou SEGUCE ?</summary><p>Il les complète. GUCE, SEGUCE et SYDONIA restent indispensables pour les démarches officielles ; Jalon prend en charge ce qui les entoure : vos dossiers, vos équipes, vos coûts et la relation avec vos clients.</p></details>
<details><summary>Mon client a-t-il besoin d’un compte ?</summary><p>Non. Il peut suivre son dossier avec un simple code de suivi. Vous pouvez aussi lui ouvrir un portail en lecture seule.</p></details>
<details><summary>Quelle différence entre solo et agence ?</summary><p>Le transitaire solo crée son espace lui-même et travaille seul. L’agence envoie une demande, nous la contactons, puis Jalon est installé pour elle avec son équipe et ses rôles.</p></details>
<details><summary>Où sont stockés mes documents ?</summary><p>Dans un espace privé du serveur, jamais accessible par un lien public. L’accès est contrôlé selon vos droits.</p></details>
<details><summary>Quel est le prix ?</summary><p>Nous vous présentons l’offre lors de la prise de contact, selon la taille de votre structure.</p></details>
</div>
</section>

<section class="vt" style="padding-top:8px">
<div class="vt-final" data-r>
<h2>Prêt à voir où en sont vos dossiers ?</h2>
<p>Créez votre espace, ou connectez-vous si vous en avez déjà un.</p>
<div class="vt-actions"><x-bouton :href="route('inscription')">Commencer</x-bouton><x-bouton :href="route('connexion')" variante="encre">Connexion</x-bouton></div>
</div>
</section>
</main>

<footer class="vt-pied">
<div class="vt">
<div class="vt-pied-haut">
<div class="vt-marque">
<x-logo :hauteur="26" />
<p>Le suivi des dossiers de transit et de douane, du premier document à la livraison.</p>
<span class="lieu">Lomé, Togo</span>
</div>

<div class="vt-col">
<h4>Produit</h4>
<ul>
<li><a href="#parcours">Le parcours</a></li>
<li><a href="#apercu">Aperçu de l’interface</a></li>
<li><a href="#fonctions">Fonctions</a></li>
<li><a href="#faq">Questions fréquentes</a></li>
</ul>
</div>

<div class="vt-col">
<h4>Compte</h4>
<ul>
<li><a href="{{ route('connexion') }}">Connexion</a></li>
<li><a href="{{ route('inscription') }}">Créer mon espace</a></li>
<li><a href="{{ route('inscription', ['vous' => 'agence']) }}">Demande d’agence</a></li>
<li><a href="{{ route('suivi') }}">Suivre un dossier</a></li>
</ul>
</div>

<div class="vt-col">
<h4>Informations</h4>
<ul>
<li><a href="{{ route('conditions') }}">Conditions d’utilisation</a></li>
<li><a href="mailto:contact@jalon.tg">contact@jalon.tg</a></li>
</ul>
</div>
</div>

<div class="vt-pied-bas">
<span class="droits">© Jalon, tous droits réservés</span>
<span>Données de démonstration · Interface en français</span>
<a href="#haut">Haut de page ↑</a>
</div>
</div>
</footer>

<script>
(function () {
  var io = new IntersectionObserver(function (entrees) {
    entrees.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('vu'); io.unobserve(e.target); }
    });
  }, { threshold: .15 });
  document.querySelectorAll('[data-r]').forEach(function (el) { io.observe(el); });

  var barre = document.getElementById('barre');
  addEventListener('scroll', function () { barre.classList.toggle('defile', scrollY > 8); }, { passive: true });

  var lb = document.getElementById('lb'), lbi = lb.querySelector('img');
  document.querySelectorAll('[data-zoom]').forEach(function (b) {
    b.addEventListener('click', function () {
      lbi.src = b.dataset.zoom; lb.hidden = false; lb.scrollTop = 0; document.body.style.overflow = 'hidden';
    });
  });
  function fermer() { lb.hidden = true; document.body.style.overflow = ''; }
  lb.addEventListener('click', fermer);
  addEventListener('keydown', function (e) { if (e.key === 'Escape') fermer(); });

  var items = document.querySelectorAll('#pcListe li');
  var num = document.getElementById('pcNum');
  var bar = document.getElementById('pcBarre');
  var io2 = new IntersectionObserver(function (entrees) {
    entrees.forEach(function (e) {
      if (!e.isIntersecting) { return; }
      var i = Array.prototype.indexOf.call(items, e.target);
      items.forEach(function (li, k) { li.classList.toggle('actif', k === i); });
      num.textContent = ('0' + (i + 1)).slice(-2);
      bar.style.width = ((i + 1) / items.length * 100) + '%';
    });
  }, { rootMargin: '-45% 0px -45% 0px' });
  items.forEach(function (li) { io2.observe(li); });
})();
</script>
</body>
</html>
