<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jalon · Le suivi des dossiers de transit, du premier document à la livraison</title>
<meta name="description" content="Jalon réunit dossiers, conteneurs, documents et factures dans une seule application, pensée pour les transitaires du Port de Lomé. Chaque jalon daté, chaque client informé.">
<script>
document.documentElement.classList.add('js');
</script>
@vite(['resources/css/global.css', 'resources/js/app.js'])
<style>
/* ==========================================================================
   Vitrine Jalon — charte de l'application, néomorphisme très discret.
   ========================================================================== */
html{scroll-behavior:smooth}
body{margin:0;background:var(--c-page);color:var(--c-ink);overflow-x:hidden;-webkit-font-smoothing:antialiased}
.vt{max-width:1180px;margin:0 auto;padding:0 24px}
.vt a{text-underline-offset:3px}
section[id]{scroll-margin-top:86px}
:root{--ease:cubic-bezier(.2,.7,.1,1);--vt-hair:1px solid var(--c-line)}

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

/* ---------- Navigation ---------- */
.vt-barre{position:sticky;top:0;z-index:30;background:color-mix(in srgb,var(--c-page) 88%,transparent);backdrop-filter:blur(16px);border-bottom:1px solid transparent;transition:border-color .3s,background .3s}
.vt-barre.defile{border-bottom-color:var(--c-line);background:color-mix(in srgb,var(--c-page) 95%,transparent)}
.vt-nav{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:28px;padding:15px 0}
.vt-liens{display:flex;align-items:center;justify-content:center;gap:28px}
.vt-liens a{position:relative;color:var(--c-ink-2);text-decoration:none;font-size:.93rem;font-weight:500;padding:4px 0}
.vt-liens a::after{content:"";position:absolute;left:0;right:0;bottom:-2px;height:1px;background:var(--c-brand);transform:scaleX(0);transform-origin:left;transition:transform .3s var(--ease)}
.vt-liens a:hover{color:var(--c-ink)}
.vt-liens a:hover::after{transform:scaleX(1)}
.vt-nav-actions{display:flex;align-items:center;gap:16px}
.vt-nav-actions a:not(.btn){color:var(--c-ink-2);text-decoration:none;font-size:.93rem;font-weight:500}
.vt-nav-actions a:not(.btn):hover{color:var(--c-brand)}

/* ---------- Héro ---------- */
.vt-hero{position:relative;display:grid;grid-template-columns:1.02fr .98fr;gap:60px;align-items:center;padding:72px 0 76px}
.vt-halo{position:absolute;inset:-140px -8vw auto;height:620px;z-index:-1;pointer-events:none;overflow:hidden}
.vt-halo i{position:absolute;display:block;border-radius:50%;filter:blur(90px);opacity:.5}
.vt-halo i:first-child{width:520px;height:420px;right:4%;top:24px;background:color-mix(in srgb,var(--c-brand) 15%,transparent);animation:derive 22s ease-in-out infinite}
.vt-halo i:last-child{width:360px;height:320px;left:2%;top:190px;background:color-mix(in srgb,var(--c-teal) 8%,transparent);animation:derive 26s ease-in-out infinite reverse}
.vt-eyebrow{display:flex;align-items:center;gap:10px;margin:0 0 20px;color:var(--c-muted);font-size:.71rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase}
.vt-eyebrow::before{content:"";width:20px;height:1px;background:var(--c-brand)}
.vt h1{font-size:clamp(2.5rem,5.2vw,4.7rem);line-height:1;letter-spacing:-.045em;margin:0 0 22px;font-weight:700}
.vt h1 em{font-style:normal;color:var(--c-brand)}
.vt-lead{font-size:1.12rem;line-height:1.62;color:var(--c-ink-2);max-width:48ch;margin:0 0 28px}
.vt-actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.vt-preuves{display:flex;flex-wrap:wrap;gap:8px 22px;list-style:none;margin:0 0 30px;padding:0;color:var(--c-muted);font-size:.85rem}
.vt-preuves li{display:flex;align-items:center;gap:8px}
.vt-preuves li::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--c-green)}
.vt-suivi{display:flex;gap:8px;max-width:440px}
.vt-suivi input{flex:1;min-width:0;padding:13px 16px;border:1px solid var(--c-line);border-radius:12px;background:var(--c-surface);color:var(--c-ink);font:inherit;font-size:.95rem;text-transform:uppercase;letter-spacing:.04em;box-shadow:var(--ombre-creux)}
.vt-suivi input::placeholder{text-transform:none;letter-spacing:0;color:var(--c-muted)}
.vt-suivi input:focus-visible{outline:none;border-color:var(--c-brand);box-shadow:0 0 0 3px var(--c-brand-soft),var(--ombre-creux)}
.vt-aide{font-size:.85rem;color:var(--c-muted);margin:11px 0 0;max-width:44ch}

/* Carte dossier */
.vt-carte{position:relative;background:var(--c-surface);border:1px solid var(--c-line);border-radius:22px;padding:24px 24px 20px;box-shadow:var(--ombre-pose)}
.vt-carte-tete{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding-bottom:16px;border-bottom:var(--vt-hair)}
.vt-carte-tete small{display:block;font-size:.67rem;letter-spacing:.16em;text-transform:uppercase;color:var(--c-muted);margin-bottom:6px}
.vt-carte-tete strong{font-size:1.02rem;letter-spacing:-.01em}
.vt-puce{display:inline-flex;align-items:center;gap:7px;padding:5px 11px;border-radius:999px;background:var(--c-rose-bg);color:var(--c-rose);font-size:.73rem;font-weight:600;white-space:nowrap}
.vt-puce::before{content:"";width:5px;height:5px;border-radius:50%;background:currentColor}
.vt-jalons{list-style:none;margin:14px 0 0;padding:0}
.vt-jalons li{position:relative;display:flex;align-items:center;gap:12px;padding:7px 0;font-size:.88rem;color:var(--c-muted)}
.vt-jalons li::before{content:"";flex:none;width:13px;height:13px;border-radius:50%;border:1.5px solid var(--c-line-fort);background:var(--c-surface)}
.vt-jalons li.fait{color:var(--c-ink-2)}
.vt-jalons li.fait::before{background:var(--c-brand);border-color:var(--c-brand);box-shadow:inset 0 0 0 2px var(--c-surface)}
.vt-jalons li.actuel{color:var(--c-ink);font-weight:600}
.vt-jalons li.actuel::before{border-color:var(--c-brand);box-shadow:0 0 0 4px var(--c-brand-soft)}
.vt-carte-pied{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;padding-top:14px;border-top:var(--vt-hair);font-size:.79rem;color:var(--c-muted)}
.vt-jauge{flex:1;height:3px;border-radius:3px;background:var(--c-surface-2);box-shadow:var(--ombre-creux);overflow:hidden}
.vt-jauge i{display:block;height:100%;width:55%;background:var(--c-brand);border-radius:3px}
.vt-note{font-size:.75rem;color:var(--c-muted);margin:11px 0 0}

/* ---------- Bandeau de réassurance ---------- */
.vt-rassure{border-block:1px solid var(--c-line);padding:15px 0}
.vt-rassure ul{display:flex;flex-wrap:wrap;gap:10px 34px;list-style:none;margin:0;padding:0;color:var(--c-ink-2);font-size:.86rem}
.vt-rassure li{display:flex;align-items:center;gap:9px}
.vt-rassure li::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--c-green)}

/* ---------- Sections ---------- */
.vt-section{padding:88px 0;border-top:var(--vt-hair)}
.vt-entete{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:end;margin-bottom:38px}
.vt-entete h2{font-size:clamp(1.85rem,3.3vw,2.8rem);line-height:1.06;letter-spacing:-.035em;margin:0 0 14px;max-width:24ch}
.vt-intro{color:var(--c-muted);max-width:60ch;font-size:1.01rem;line-height:1.64;margin:0}
.vt-index{font-size:.71rem;letter-spacing:.16em;color:var(--c-muted);white-space:nowrap;padding-bottom:6px}
.vt-grille{display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:16px}

.vt-c{position:relative;background:var(--c-surface);border:1px solid var(--c-line);border-radius:16px;padding:26px;box-shadow:var(--ombre-fine);transition:box-shadow .3s,transform .4s var(--ease)}
.vt-c:hover{box-shadow:var(--ombre-relief);transform:translateY(-2px)}
.vt-c h3{margin:0 0 8px;font-size:1.05rem;letter-spacing:-.015em}
.vt-c p{margin:0;color:var(--c-ink-2);line-height:1.58;font-size:.94rem}
.vt-n{display:block;font-size:.69rem;font-weight:600;letter-spacing:.16em;color:var(--c-brand);margin-bottom:14px}

/* ---------- Une journée avec Jalon ---------- */
.jour{display:grid;margin-top:4px}
.jour article{display:grid;grid-template-columns:.46fr .54fr;gap:48px;align-items:center;padding:44px 0;border-top:var(--vt-hair)}
.jour article:last-child{border-bottom:var(--vt-hair)}
.jour article:nth-child(even){grid-template-columns:.54fr .46fr}
.jour article:nth-child(even) .jour-texte{order:2}
.jour-heure{display:flex;align-items:center;gap:10px;font-size:.71rem;letter-spacing:.16em;text-transform:uppercase;color:var(--c-muted);margin:0 0 14px}
.jour-heure b{color:var(--c-brand);font-weight:600}
.jour h3{margin:0 0 10px;font-size:clamp(1.24rem,2.2vw,1.7rem);letter-spacing:-.028em;line-height:1.15}
.jour p{margin:0;color:var(--c-ink-2);line-height:1.6;font-size:.96rem;max-width:44ch}
.jour-carte{background:var(--c-surface);border:1px solid var(--c-line);border-radius:16px;padding:18px 20px;box-shadow:var(--ombre-fine)}
.jour-carte .tete{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:12px;border-bottom:var(--vt-hair);font-size:.78rem;color:var(--c-muted)}
.jour-carte .tete strong{color:var(--c-ink);font-size:.9rem;font-weight:600}
.jour-lignes{list-style:none;margin:12px 0 0;padding:0;display:grid;gap:9px;font-size:.86rem;color:var(--c-ink-2)}
.jour-lignes li{display:flex;align-items:center;gap:10px}
.jour-lignes .pt{width:6px;height:6px;border-radius:50%;background:var(--c-brand);flex:none}
.jour-lignes .pt--ok{background:var(--c-green)}
.jour-lignes .pt--vide{background:var(--c-line-fort)}
.jour-lignes .fin{margin-left:auto;color:var(--c-muted);font-size:.8rem;white-space:nowrap}
.jour-bas{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding-top:12px;border-top:var(--vt-hair);font-size:.8rem;color:var(--c-muted)}

/* ---------- Galerie épinglée ---------- */
.gx{display:grid;grid-template-columns:1.12fr .88fr;gap:52px;align-items:start}
.gx-visuel{position:sticky;top:92px}
.gx-cadre{border:1px solid var(--c-line);border-radius:18px;background:var(--c-surface);overflow:hidden;box-shadow:var(--ombre-pose)}
.gx-ouvrir{display:block;width:100%;padding:0;border:0;background:var(--c-surface-2);cursor:zoom-in}
.gx-images{position:relative;display:block;aspect-ratio:16 / 11;background:var(--c-surface-2)}
.gx-img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:top;opacity:0;transition:opacity .45s var(--ease)}
.gx-img.actif{opacity:1}
.gx-pied{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:11px 15px;border-top:var(--vt-hair);color:var(--c-muted);font-size:.78rem}
.gx-compteur{font-variant-numeric:tabular-nums;letter-spacing:.1em}
.gx-compteur b{color:var(--c-brand);font-weight:600}
.gx-texte{list-style:none;margin:0;padding:0}
.gx-texte li{min-height:54vh;display:flex;flex-direction:column;justify-content:center;padding:20px 0;opacity:.32;transition:opacity .45s var(--ease)}
.gx-texte li.actif{opacity:1}
.gx-texte h3{font-size:clamp(1.3rem,2.2vw,1.75rem);letter-spacing:-.028em;line-height:1.16;margin:0 0 12px}
.gx-texte p{color:var(--c-ink-2);line-height:1.6;margin:0;font-size:.95rem}
.gx-texte ul{margin:14px 0 0;padding:0;list-style:none;color:var(--c-muted);font-size:.88rem;line-height:1.6}
.gx-texte li li{display:flex;gap:10px;padding:4px 0;min-height:0}
.gx-texte li li::before{content:"";flex:none;width:5px;height:5px;margin-top:.55em;border-radius:50%;background:var(--c-brand);opacity:.65}
.gx-mini{display:none}
.ph{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:30px;align-items:start;margin-top:26px}
.ph figure{margin:0;text-align:center}
.ph-i{border:1px solid var(--c-line);border-radius:24px;overflow:hidden;box-shadow:var(--ombre-pose);max-width:240px;margin:0 auto 14px;background:var(--c-surface)}
.ph-i img{display:block;width:100%;height:auto}
.ph figcaption{color:var(--c-muted);font-size:.86rem;line-height:1.55;max-width:30ch;margin:0 auto}
.ph figcaption b{display:block;color:var(--c-ink);font-size:.93rem;margin-bottom:3px}
.lb{position:fixed;inset:0;z-index:60;background:color-mix(in srgb,#000 84%,transparent);overflow:auto;padding:32px;cursor:zoom-out;animation:fondu .25s}
.lb[hidden]{display:none}
.lb img{display:block;max-width:min(1240px,100%);margin:0 auto;border-radius:12px;height:auto;box-shadow:var(--ombre-pose)}

/* ---------- Prévu ---------- */
.pv{display:flex;flex-wrap:wrap;gap:10px}
.pv span{padding:9px 15px;border:1px dashed var(--c-line-fort);border-radius:999px;color:var(--c-ink-2);font-size:.87rem;font-weight:500;background:color-mix(in srgb,var(--c-surface) 60%,transparent)}

/* ---------- Modules ---------- */
.mod{display:grid;grid-template-columns:repeat(3,1fr);gap:38px}
.mod-famille h3{margin:0 0 4px;font-size:.72rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--c-brand)}
.mod-famille > p{margin:0 0 14px;color:var(--c-muted);font-size:.86rem}
.mod ul{list-style:none;margin:0;padding:0}
.mod li{padding:15px 0;border-top:var(--vt-hair)}
.mod b{display:block;font-weight:600;font-size:.95rem;letter-spacing:-.01em;margin-bottom:3px}
.mod span{display:block;color:var(--c-muted);font-size:.87rem;line-height:1.5}

/* ---------- Sécurité ---------- */
.secu{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;border-top:var(--vt-hair);padding-top:26px}
.secu b{display:block;font-size:.93rem;margin-bottom:5px}
.secu p{margin:0;color:var(--c-muted);font-size:.86rem;line-height:1.55}
.secu-lien{margin:18px 0 0;font-size:.88rem}

/* ---------- Offres ---------- */
.offres{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
.offre{display:flex;flex-direction:column;gap:18px;padding:30px;background:var(--c-surface);border:1px solid var(--c-line);border-radius:18px;box-shadow:var(--ombre-fine)}
.offre--agence{background:color-mix(in srgb,var(--c-brand-soft) 42%,var(--c-surface));border-color:color-mix(in srgb,var(--c-brand) 18%,var(--c-line))}
.offre h3{margin:0;font-size:1.2rem;letter-spacing:-.02em}
.offre .pour{margin:-8px 0 0;color:var(--c-muted);font-size:.9rem}
.offre ul{list-style:none;margin:0;padding:0;display:grid;gap:9px;color:var(--c-ink-2);font-size:.92rem}
.offre li{display:flex;gap:10px}
.offre li::before{content:"✓";color:var(--c-brand);font-weight:700;line-height:1.4}
.offre .cta{margin-top:auto}
.offre-note{margin:16px 0 0;color:var(--c-muted);font-size:.85rem}

/* ---------- Chiffres ---------- */
.chiffres{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;border-top:var(--vt-hair);padding-top:26px}
.chiffres strong{display:block;font-size:2rem;letter-spacing:-.045em;color:var(--c-brand);font-variant-numeric:tabular-nums;line-height:1}
.chiffres span{display:block;margin-top:6px;color:var(--c-muted);font-size:.85rem}

/* ---------- Pionniers ---------- */
.pionniers{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;border-top:var(--vt-hair);padding-top:26px}
.pionniers i{display:block;font-style:normal;font-size:.7rem;font-weight:600;letter-spacing:.16em;color:var(--c-brand);margin-bottom:9px}
.pionniers b{display:block;font-size:.98rem;margin-bottom:6px}
.pionniers p{margin:0;color:var(--c-muted);font-size:.88rem;line-height:1.55}

/* ---------- Bandeau CTA ---------- */
.bande-cta{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:24px 28px;border:1px solid var(--c-line);border-radius:16px;background:var(--c-surface);box-shadow:var(--ombre-fine);margin-top:44px}
.bande-cta p{margin:0;font-weight:600;letter-spacing:-.015em}
.bande-cta small{display:block;color:var(--c-muted);font-weight:400;font-size:.85rem;margin-top:3px}

/* ---------- FAQ ---------- */
.fq{max-width:880px}
.fq details{border-top:var(--vt-hair);padding:19px 0}
.fq details:last-of-type{border-bottom:var(--vt-hair)}
.fq summary{cursor:pointer;font-weight:600;font-size:.98rem;list-style:none;display:flex;justify-content:space-between;gap:20px;align-items:center}
.fq summary::-webkit-details-marker{display:none}
.fq summary::after{content:"+";color:var(--c-brand);font-size:1.3rem;line-height:1;transition:transform .3s}
.fq details[open] summary::after{transform:rotate(45deg)}
.fq p{color:var(--c-ink-2);line-height:1.62;max-width:74ch;margin:13px 0 0;font-size:.94rem}

/* ---------- Vie discrète du héro, rail et caméo ---------- */
.vt-puce{animation:puceSouffle 5s ease-in-out infinite}
@keyframes puceSouffle{
  0%,100%{box-shadow:0 0 0 0 color-mix(in srgb,var(--c-rose) 26%,transparent)}
  50%{box-shadow:0 0 0 6px transparent}
}
.vt-jalons li.actuel::before{animation:jalonSouffle 3.2s ease-in-out infinite}
@keyframes jalonSouffle{
  0%,100%{box-shadow:0 0 0 4px var(--c-brand-soft)}
  50%{box-shadow:0 0 0 7px var(--c-brand-soft)}
}
.vt-jauge i{transition:width .7s var(--ease)}
#carteJalons{transition:opacity .22s linear}
#carteJalons.retour{opacity:.4}

.rail{margin:0 0 10px;padding:22px 0 6px;border-top:var(--vt-hair)}
.rail-scene{position:relative;height:64px}
.rail-voie{position:absolute;left:0;right:0;top:22px;height:1px;background:var(--c-line)}
.rail-point{position:absolute;top:22px;width:9px;height:9px;border-radius:50%;background:var(--c-surface);border:1px solid var(--c-line-fort);transform:translate(-50%,-50%)}
.rail-point::after{content:"";position:absolute;inset:-5px;border-radius:50%;border:1px solid var(--c-brand);opacity:0;animation:railPulse 10s linear infinite}
.rail-point:nth-child(3)::after{animation-delay:0s}
.rail-point:nth-child(4)::after{animation-delay:2.4s}
.rail-point:nth-child(5)::after{animation-delay:4.8s}
.rail-point:nth-child(6)::after{animation-delay:7.2s}
@keyframes railPulse{
  0%{opacity:0;transform:scale(.6)}
  6%{opacity:.55;transform:scale(1)}
  22%{opacity:0;transform:scale(1.7)}
  100%{opacity:0;transform:scale(1.7)}
}
.rail-conteneur{position:absolute;top:22px;left:6%;width:46px;height:22px;margin:-11px 0 0 -23px;border-radius:4px;background:var(--c-brand);box-shadow:var(--ombre-fine);animation:railTrajet 10s var(--ease) infinite}
.rail-conteneur::before{content:"";position:absolute;inset:4px 9px;border-left:2px solid color-mix(in srgb,#fff 55%,transparent);border-right:2px solid color-mix(in srgb,#fff 55%,transparent)}
@keyframes railTrajet{
  0%,8%{left:6%;opacity:1}
  26%,34%{left:34%}
  56%,64%{left:63%}
  86%,92%{left:92%;opacity:1}
  100%{left:92%;opacity:0}
}
.rail-mot{position:absolute;top:36px;transform:translateX(-50%);color:var(--c-muted);font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;white-space:nowrap}
.cameo{width:228px;max-width:40vw;color:var(--c-ink-2);opacity:.9;align-self:flex-end;margin-bottom:2px}
.cameo svg{display:block;width:100%;height:auto}
.cameo .trait{fill:none;stroke:currentColor;stroke-width:1.6;stroke-linecap:round}
.cameo .sol{stroke:var(--c-line-fort);stroke-width:1}
.cameo .porte{fill:none;stroke:currentColor;stroke-width:1.2;opacity:.45}
.cameo .accent{fill:none;stroke:var(--c-brand);stroke-width:1.6}
.cameo .accent-fin{fill:none;stroke:var(--c-brand);stroke-width:1.2;stroke-linecap:round}

/* ---------- Appel final ---------- */
.vt-final{position:relative;overflow:hidden;margin:0 auto 8px;padding:72px 40px;text-align:center;border-radius:26px;background:var(--c-ink);color:var(--c-page);box-shadow:var(--ombre-pose)}
.vt-final::before{content:"";position:absolute;inset:-40% -10% auto;height:520px;background:radial-gradient(50% 60% at 50% 0%,color-mix(in srgb,var(--c-brand) 46%,transparent),transparent 72%);opacity:.5;pointer-events:none}
.vt-final h2{position:relative;margin:0 auto 14px;max-width:20ch;color:var(--c-page);font-size:clamp(1.8rem,3.5vw,2.6rem);letter-spacing:-.035em;line-height:1.08}
.vt-final p{position:relative;margin:0 auto 28px;max-width:46ch;opacity:.72}
.vt-final .vt-actions{position:relative;justify-content:center;margin:0}

/* ---------- Pied de page ---------- */
.vt-pied{border-top:var(--vt-hair);margin-top:60px;background:color-mix(in srgb,var(--c-surface-2) 55%,var(--c-page))}
.vt-pied-haut{display:grid;grid-template-columns:1.5fr repeat(3,.85fr);gap:40px;padding:54px 0 42px}
.vt-marque p{margin:16px 0 0;color:var(--c-ink-2);font-size:.91rem;line-height:1.6;max-width:32ch}
.vt-marque .lieu{display:inline-flex;align-items:center;gap:8px;margin-top:16px;color:var(--c-muted);font-size:.79rem}
.vt-marque .lieu::before{content:"";width:5px;height:5px;border-radius:50%;background:var(--c-brand)}
.vt-col h4{margin:0 0 15px;font-size:.69rem;font-weight:600;letter-spacing:.16em;text-transform:uppercase;color:var(--c-muted)}
.vt-col ul{list-style:none;margin:0;padding:0}
.vt-col li{margin:0 0 10px}
.vt-col a{color:var(--c-ink-2);text-decoration:none;font-size:.89rem}
.vt-col a:hover{color:var(--c-brand)}
.vt-pied-bas{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:17px 0 21px;border-top:var(--vt-hair);color:var(--c-muted);font-size:.81rem}
.vt-pied-bas a{color:var(--c-muted);text-decoration:none}
.vt-pied-bas a:hover{color:var(--c-brand)}
.vt-pied-bas .droits{color:var(--c-ink-2);font-weight:500}

/* ---------- Responsive ---------- */
@media (max-width:980px){
  .vt-hero{grid-template-columns:1fr;gap:34px;padding:44px 0 56px}
  .vt-nav{grid-template-columns:1fr auto;gap:14px}
  .vt-liens{display:none}
  .jour article,.jour article:nth-child(even){grid-template-columns:1fr;gap:20px;padding:32px 0}
  .jour article:nth-child(even) .jour-texte{order:0}
  .gx{grid-template-columns:1fr;gap:0}
  .gx-visuel{display:none}
  .gx-texte li{min-height:0;opacity:1;padding:26px 0;border-top:var(--vt-hair)}
  .gx-texte li:first-child{border-top:0;padding-top:0}
  .gx-mini{display:block;width:100%;padding:0;border:1px solid var(--c-line);border-radius:14px;overflow:hidden;background:var(--c-surface);box-shadow:var(--ombre-fine);margin-bottom:18px;cursor:zoom-in}
  .gx-mini img{display:block;width:100%;height:auto;max-height:340px;object-fit:cover;object-position:top}
  .mod{grid-template-columns:1fr;gap:26px}
  .secu,.chiffres{grid-template-columns:repeat(2,1fr);gap:18px}
  .pionniers{grid-template-columns:1fr;gap:18px}
  .offres{grid-template-columns:1fr}
  .vt-entete{grid-template-columns:1fr}
  .vt-index{display:none}
  .cameo{display:none}
  .rail-mot{font-size:.62rem;letter-spacing:.1em}
  .vt-pied-haut{grid-template-columns:1fr 1fr;gap:30px;padding:38px 0 30px}
  .vt-marque{grid-column:1/-1}
  .vt-section{padding:54px 0}
  .vt-final{padding:50px 24px}
}
@media (max-width:560px){
  .vt-pied-haut{grid-template-columns:1fr}
  .vt-nav-actions{gap:12px}
  .vt-nav-actions a:not(.btn){font-size:.86rem}
  .vt-rassure ul{gap:8px 20px;font-size:.82rem}
}
</style>
</head>
<body id="haut">
@php
    // WhatsApp si un numéro est configuré, sinon demande de démonstration par e-mail :
    // ce bouton ne renvoie jamais vers le formulaire d'inscription.
    $whatsapp = preg_replace('/\D/', '', (string) config('app.whatsapp'));
    $demoExterne = $whatsapp !== '';
    $lienDemo = $demoExterne
        ? 'https://wa.me/'.$whatsapp.'?text='.rawurlencode('Bonjour, je souhaite une démonstration de Jalon pour mon agence.')
        : 'mailto:contact@jalon.tg?subject='.rawurlencode('Demande de démonstration Jalon');
@endphp

<div class="vt-barre" id="barre"><div class="vt"><div class="vt-nav">
<a href="{{ url('/') }}" aria-label="Jalon, accueil"><x-logo :hauteur="28" /></a>
<nav class="vt-liens" aria-label="Navigation principale">
<a href="#solution">La solution</a>
<a href="#journee">Une journée</a>
<a href="#apercu">Aperçu</a>
<a href="#modules">Modules</a>
<a href="#offres">Offres</a>
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
<p class="vt-eyebrow" data-r>Suivi de transit et de douane · Port de Lomé</p>
<h1>
<span class="ln"><span style="--i:0">Vos dossiers avancent.</span></span>
<span class="ln"><span style="--i:1"><em>Jalon en garde</em></span></span>
<span class="ln"><span style="--i:2">la trace.</span></span>
</h1>
<p class="vt-lead" data-r style="--d:4">Dossiers, conteneurs, documents, factures et clients dans une seule application, pensée pour les transitaires du Port de Lomé. Chaque étape datée, chaque importateur informé.</p>
<div class="vt-actions" data-r style="--d:5">
<x-bouton :href="route('inscription')">Créer mon espace</x-bouton>
<x-bouton :href="$lienDemo" variante="encre">Demander une démo</x-bouton>
</div>
<ul class="vt-preuves" data-r style="--d:5">
<li>Sans engagement</li>
<li>Interface en français</li>
<li>Support à Lomé</li>
</ul>
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
<ol class="vt-jalons" id="carteJalons">
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
<div class="vt-carte-pied"><span id="carteEtape">Étape 6 sur 9</span><span class="vt-jauge" aria-hidden="true"><i id="carteJauge"></i></span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</aside>
</section>

<div class="vt-rassure"><div class="vt">
<ul>
<li>Connexion chiffrée</li>
<li>Données isolées par agence</li>
<li>Documents en stockage privé</li>
<li>Prêt en trois étapes</li>
</ul>
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

<section class="vt vt-section" id="journee">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Une journée avec Jalon</p>
<h2 data-r>De l’arrivée du B/L au bon à enlever.</h2>
<p class="vt-intro" data-r>Suivez le dossier JL-2027-0031. Chaque moment montre ce que Jalon enregistre et ce que votre client voit.</p>
</div>
<span class="vt-index">02 / 07</span>
</div>

<div class="rail" data-r aria-hidden="true">
<div class="rail-scene">
<span class="rail-voie"></span>
<span class="rail-conteneur"></span>
<span class="rail-point" style="left:6%"></span>
<span class="rail-point" style="left:34%"></span>
<span class="rail-point" style="left:63%"></span>
<span class="rail-point" style="left:92%"></span>
<span class="rail-mot" style="left:6%">Port</span>
<span class="rail-mot" style="left:34%">Douane</span>
<span class="rail-mot" style="left:63%">Droits</span>
<span class="rail-mot" style="left:92%">Livraison</span>
</div>
</div>

<div class="jour">
<article>
<div class="jour-texte" data-r>
<p class="jour-heure"><b>07:40</b> · Le matin</p>
<h3>Le dossier est ouvert.</h3>
<p>Référence et code de suivi générés, pièces attendues listées selon le régime douanier. Le client reçoit son code, sans créer de compte.</p>
</div>
<div class="jour-carte" data-r style="--d:1">
<div class="tete"><strong>JL-2027-0031</strong><span>Régime : consommation</span></div>
<ul class="jour-lignes">
<li><span class="pt pt--ok"></span>Facture commerciale<span class="fin">reçue</span></li>
<li><span class="pt pt--ok"></span>Packing list<span class="fin">reçue</span></li>
<li><span class="pt pt--vide"></span>B/L<span class="fin">attendu</span></li>
</ul>
<div class="jour-bas"><span>3 conteneurs</span><span>Code : JLK-4821</span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</div>
</article>

<article>
<div class="jour-texte" data-r>
<p class="jour-heure"><b>10:20</b> · La matinée</p>
<h3>Les documents sont validés.</h3>
<p>Chaque pièce est cochée par l’équipe. Le client ne voit que ce que vous choisissez de partager, et le téléchargement passe par un contrôle des droits.</p>
</div>
<div class="jour-carte" data-r style="--d:1">
<div class="tete"><strong>Documents</strong><span>4 sur 5 validés</span></div>
<ul class="jour-lignes">
<li><span class="pt pt--ok"></span>ECTN / BESC<span class="fin">validé</span></li>
<li><span class="pt pt--ok"></span>Manifeste<span class="fin">validé</span></li>
<li><span class="pt pt--vide"></span>Certificat d’origine<span class="fin">à fournir</span></li>
</ul>
<div class="jour-bas"><span>Partagé au client</span><span>3 pièces</span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</div>
</article>

<article>
<div class="jour-texte" data-r>
<p class="jour-heure"><b>14:30</b> · L’après-midi</p>
<h3>L’inspection est datée.</h3>
<p>Le passage de jalon se fait en un clic, avec sa condition remplie et son commentaire. La franchise de chaque conteneur est recalculée au même moment.</p>
</div>
<div class="jour-carte" data-r style="--d:1">
<div class="tete"><strong>Jalon 6 · Inspection</strong><span>Validé à 14:31</span></div>
<ul class="jour-lignes">
<li><span class="pt pt--ok"></span>Déclaration soumise<span class="fin">hier</span></li>
<li><span class="pt pt--ok"></span>Inspection<span class="fin">aujourd’hui</span></li>
<li><span class="pt pt--vide"></span>Droits à payer<span class="fin">à venir</span></li>
</ul>
<div class="jour-bas"><span>Franchise conteneur</span><span>J-2</span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</div>
</article>

<article>
<div class="jour-texte" data-r>
<p class="jour-heure"><b>17:15</b> · La fin de journée</p>
<h3>Le bon à enlever est signé.</h3>
<p>La facture part avec ses lignes et ses acomptes. Le client suit l’avancement depuis son code et retrouve ses documents au même endroit.</p>
</div>
<div class="jour-carte" data-r style="--d:1">
<div class="tete"><strong>Facture FA-2027-0142</strong><span>Partiellement payée</span></div>
<ul class="jour-lignes">
<li><span class="pt pt--ok"></span>Honoraires<span class="fin">185 000 F</span></li>
<li><span class="pt pt--ok"></span>Frais portuaires<span class="fin">92 500 F</span></li>
<li><span class="pt pt--ok"></span>Acompte reçu<span class="fin">-100 000 F</span></li>
</ul>
<div class="jour-bas"><span>Solde à venir</span><span>177 500 F</span></div>
<p class="vt-note">Exemple d’illustration, données fictives.</p>
</div>
</article>
</div>

<div class="bande-cta" data-r>
<p>Vous voulez voir ce parcours sur vos propres dossiers ?<small>Une démonstration de 20 minutes suffit pour décider.</small></p>
<x-bouton :href="$lienDemo" taille="petit">Demander une démo</x-bouton>
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
<div class="gx">
<div class="gx-visuel" data-r>
<div class="gx-cadre">
<div class="sh-b"><i></i><i></i><i></i></div>
<button type="button" class="gx-ouvrir" id="gxOuvrir" data-zoom="{{ asset('img/captures/'.$ecrans[0][0].'.webp') }}" aria-label="Agrandir l’aperçu">
<span class="gx-images">
@foreach ($ecrans as $i => $e)
<img class="gx-img @if ($i === 0) actif @endif" src="{{ asset('img/captures/'.$e[0].'.webp') }}" alt="{{ $e[2] }}" @if ($i > 0) loading="lazy" @endif decoding="async">
@endforeach
</span>
</button>
<div class="gx-pied"><span class="gx-compteur"><b id="gxNum">01</b> / {{ str_pad(count($ecrans), 2, '0', STR_PAD_LEFT) }}</span><span>Cliquez pour agrandir</span></div>
</div>
<p class="vt-note">Maquettes filigranées, données fictives.</p>
</div>

<ol class="gx-texte" id="gxListe">
@foreach ($ecrans as $i => $e)
<li data-ecran="{{ $i }}" class="@if ($i === 0) actif @endif">
<button type="button" class="gx-mini" data-zoom="{{ asset('img/captures/'.$e[0].'.webp') }}" aria-label="Agrandir : {{ $e[2] }}"><img src="{{ asset('img/captures/'.$e[0].'.webp') }}" alt="{{ $e[2] }}" loading="lazy" decoding="async"></button>
<p class="vt-eyebrow">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} · {{ $e[1] }}</p>
<h3>{{ $e[2] }}</h3>
<p>{{ $e[3] }}</p>
<ul>@foreach ($e[4] as $pt)<li>{{ $pt }}</li>@endforeach</ul>
</li>
@endforeach
</ol>
</div>

<div style="margin-top:64px">
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

<div class="bande-cta" data-r>
<p>Prêt à voir vos dossiers dans cette interface ?<small>Votre espace se crée en quelques minutes.</small></p>
<x-bouton :href="route('inscription')" taille="petit">Créer mon espace</x-bouton>
</div>
</section>
<div class="lb" id="lb" hidden><img alt=""></div>

<section class="vt vt-section" id="modules">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Les modules</p>
<h2 data-r>Huit modules, une seule base de données.</h2>
<p class="vt-intro" data-r>Chaque module partage les mêmes dossiers, clients et documents : rien n’est saisi deux fois.</p>
</div>
<span class="vt-index">04 / 07</span>
</div>
<div class="mod">
<div class="mod-famille" data-r>
<h3>Suivre</h3>
<p>Le dossier, son avancement, ses conteneurs</p>
<ul>
<li><b>Dossiers</b><span>Recherche par référence, client, B/L ou conteneur, archivage</span></li>
<li><b>Jalons et documents</b><span>9 étapes datées, pièces exigées selon le régime, validation</span></li>
<li><b>Conteneurs et franchise</b><span>Statut, fin de franchise, rappels J-2, J-1 et J0</span></li>
</ul>
</div>
<div class="mod-famille" data-r style="--d:1">
<h3>Facturer</h3>
<p>Les coûts, les factures et la relation client</p>
<ul>
<li><b>Coûts et marge</b><span>Débours et frais saisis par dossier, marge prévue puis réelle</span></li>
<li><b>Facturation</b><span>Lignes multiples, paiements partiels, solde, version imprimable</span></li>
<li><b>Clients et portail</b><span>Fiches, solde dû, accès en lecture seule, suivi par code</span></li>
</ul>
</div>
<div class="mod-famille" data-r style="--d:2">
<h3>Piloter</h3>
<p>L’agence, les chiffres et les accès</p>
<ul>
<li><b>Tableau de bord</b><span>Chiffre d’affaires, volume, marge et prochaines échéances</span></li>
<li><b>Exports et journal</b><span>CSV lisibles dans Excel, historique d’activité de l’équipe</span></li>
<li><b>Équipe et paramètres</b><span>Rôles, permissions, workflow des jalons, alertes de l’agence</span></li>
</ul>
</div>
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
<div class="secu">
<div data-r><b>Connexion chiffrée</b><p>Les échanges avec l’application sont protégés en transit.</p></div>
<div data-r style="--d:1"><b>Données isolées</b><p>Chaque agence ne voit que ses dossiers, clients et factures.</p></div>
<div data-r style="--d:2"><b>Permissions par rôle</b><p>Administrateur, transitaire, comptable : chacun accède à ce qui le concerne.</p></div>
<div data-r style="--d:3"><b>Documents privés</b><p>Stockage non public, téléchargement après vérification des droits.</p></div>
</div>
<p class="secu-lien"><a href="{{ route('conditions') }}">Conditions d’utilisation et propriété intellectuelle</a></p>
</section>

<section class="vt vt-section" id="offres">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Deux façons de démarrer</p>
<h2 data-r>Seul ou avec votre équipe.</h2>
<p class="vt-intro" data-r>Jalon s’installe selon la taille de votre structure. Aucune carte bancaire demandée pour créer un espace.</p>
</div>
<span class="vt-index">06 / 07</span>
</div>
<div class="offres">
<article class="offre" data-r>
<h3>Transitaire solo</h3>
<p class="pour">Vous travaillez seul</p>
<ul>
<li>Votre espace créé en quelques minutes</li>
<li>Dossiers, conteneurs, coûts et facturation</li>
<li>Suivi client par code, sans compte</li>
<li>Exports CSV et journal d’activité</li>
</ul>
<div class="cta"><x-bouton :href="route('inscription')">Créer mon espace</x-bouton></div>
</article>
<article class="offre offre--agence" data-r style="--d:1">
<h3>Agence de transit</h3>
<p class="pour">Vous avez une équipe</p>
<ul>
<li>Rôles, permissions et invitations par e-mail</li>
<li>Portail client en lecture seule</li>
<li>Installation accompagnée par notre équipe</li>
<li>Facturation et workflow à vos règles</li>
</ul>
<div class="cta"><x-bouton :href="$lienDemo" variante="encre">Demander une démo</x-bouton></div>
</article>
</div>
<p class="offre-note">Les tarifs sont présentés lors de la prise de contact, selon la taille de votre structure.</p>
</section>

<section class="vt vt-section" id="agences-pilotes">
<div class="vt-entete">
<div>
<p class="vt-eyebrow" data-r>Agences pionnières</p>
<h2 data-r>Les premières agences installent Jalon avec nous.</h2>
<p class="vt-intro" data-r>Vous démarrez avec l’équipe, pas avec un mode d’emploi.</p>
</div>
<div class="cameo" data-r aria-hidden="true">
<svg viewBox="0 0 220 150" role="presentation" focusable="false">
<line class="sol" x1="6" y1="132" x2="214" y2="132"/>
<rect class="porte" x="136" y="66" width="52" height="20" rx="3"/>
<rect class="trait" x="120" y="90" width="82" height="36" rx="4"/>
<line class="trait" x1="134" y1="90" x2="134" y2="126"/>
<line class="trait" x1="188" y1="90" x2="188" y2="126"/>
<circle class="trait" cx="58" cy="50" r="11"/>
<path class="trait" d="M58 61 V100"/>
<path class="trait" d="M58 100 L48 125 M58 100 L68 125"/>
<path class="trait" d="M58 72 L42 90"/>
<path class="trait" d="M58 74 L77 82"/>
<rect class="accent" x="76" y="75" width="19" height="23" rx="2"/>
<path class="accent-fin" d="M81 83 H90 M81 88 H87"/>
</svg>
</div>
</div>
<div class="pionniers">
<div data-r><i>01</i><b>Installation accompagnée</b><p>Nous paramétrons vos jalons, vos documents et votre facturation avec vous.</p></div>
<div data-r style="--d:1"><i>02</i><b>Ligne directe</b><p>Un interlocuteur unique à Lomé, joignable pendant la mise en route.</p></div>
<div data-r style="--d:2"><i>03</i><b>Vos retours comptent</b><p>Les demandes des premières agences entrent dans la feuille de route du produit.</p></div>
</div>
<div class="chiffres" style="margin-top:40px">
<div data-r><strong>9</strong><span>jalons suivis par dossier</span></div>
<div data-r style="--d:1"><strong>3</strong><span>rappels de franchise : J-2, J-1, J0</span></div>
<div data-r style="--d:2"><strong>1</strong><span>code de suivi par dossier, sans compte</span></div>
<div data-r style="--d:3"><strong>5 Mo</strong><span>par document, PDF, JPG ou PNG</span></div>
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
<details><summary>Combien de temps pour démarrer ?</summary><p>Un espace solo est utilisable le jour même. Pour une agence, comptez une session de paramétrage avec notre équipe : jalons, documents exigés, rôles et facturation.</p></details>
<details><summary>Quel est le prix ?</summary><p>Nous vous présentons l’offre lors de la prise de contact, selon la taille de votre structure et le nombre de dossiers traités.</p></details>
</div>
</section>

<section class="vt" style="padding-top:8px">
<div class="vt-final" data-r>
<h2>Ce soir, sachez où en est chaque dossier.</h2>
<p>Créez votre espace, ou parlez à quelqu’un de l’équipe. Sans engagement.</p>
<div class="vt-actions"><x-bouton :href="route('inscription')">Créer mon espace</x-bouton><x-bouton :href="$lienDemo" variante="encre">Parler à un expert</x-bouton></div>
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
<li><a href="#solution">La solution</a></li>
<li><a href="#journee">Une journée avec Jalon</a></li>
<li><a href="#apercu">Aperçu de l’interface</a></li>
<li><a href="#modules">Les modules</a></li>
<li><a href="#offres">Les offres</a></li>
</ul>
</div>

<div class="vt-col">
<h4>Compte</h4>
<ul>
<li><a href="{{ route('connexion') }}">Connexion</a></li>
<li><a href="{{ route('inscription') }}">Créer mon espace</a></li>
<li><a href="{{ $lienDemo }}"@if ($demoExterne) rel="noopener" target="_blank"@endif>Demander une démo</a></li>
<li><a href="{{ route('suivi') }}">Suivre un dossier</a></li>
</ul>
</div>

<div class="vt-col">
<h4>Informations</h4>
<ul>
<li><a href="{{ route('conditions') }}">Conditions d’utilisation</a></li>
<li><a href="mailto:contact@jalon.tg">contact@jalon.tg</a></li>
<li><a href="#faq">Questions fréquentes</a></li>
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

  // Aperçu épinglé : l'écran et sa description changent au fil du scroll.
  var gxItems = document.querySelectorAll('#gxListe > li');
  var gxImgs = document.querySelectorAll('.gx-img');
  var gxNum = document.getElementById('gxNum');
  var gxOuvrir = document.getElementById('gxOuvrir');

  function gxAfficher(i) {
    gxImgs.forEach(function (img, k) { img.classList.toggle('actif', k === i); });
    gxItems.forEach(function (li, k) { li.classList.toggle('actif', k === i); });
    if (gxNum) { gxNum.textContent = ('0' + (i + 1)).slice(-2); }
    if (gxOuvrir && gxImgs[i]) { gxOuvrir.dataset.zoom = gxImgs[i].src; }
  }

  if (gxItems.length && gxImgs.length) {
    var gxVisible = -1;
    var gxPlanifie = false;

    var gxSync = function () {
      gxPlanifie = false;
      var milieu = innerHeight * 0.5 + scrollY;
      var meilleur = 0;
      var ecart = Infinity;

      gxItems.forEach(function (li, k) {
        var r = li.getBoundingClientRect();
        var centre = r.top + scrollY + r.height / 2;
        var distance = Math.abs(centre - milieu);

        if (distance < ecart) { ecart = distance; meilleur = k; }
      });

      if (meilleur !== gxVisible) {
        gxVisible = meilleur;
        gxAfficher(meilleur);
      }
    };

    addEventListener('scroll', function () {
      if (!gxPlanifie) { gxPlanifie = true; setTimeout(gxSync, 20); }
    }, { passive: true });
    addEventListener('resize', gxSync);
    gxSync();
  }

  // Carte du héro : le dossier avance d'un jalon toutes les 4 secondes,
  // puis repart de l'étape 6 en fondu. Désactivé si le mouvement est refusé.
  var carteJalons = document.getElementById('carteJalons');

  if (carteJalons && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var jalons = carteJalons.querySelectorAll('li');
    var carteEtape = document.getElementById('carteEtape');
    var carteJauge = document.getElementById('carteJauge');
    var courant = 5;

    var carteDessiner = function (i) {
      jalons.forEach(function (li, k) {
        li.classList.toggle('fait', k < i);
        li.classList.toggle('actuel', k === i);
      });
      if (carteEtape) { carteEtape.textContent = 'Étape ' + (i + 1) + ' sur ' + jalons.length; }
      if (carteJauge) { carteJauge.style.width = Math.round(((i + 1) / jalons.length) * 100) + '%'; }
    };

    setInterval(function () {
      if (courant >= jalons.length - 1) {
        carteJalons.classList.add('retour');
        setTimeout(function () {
          courant = 5;
          carteDessiner(courant);
          carteJalons.classList.remove('retour');
        }, 260);
      } else {
        courant += 1;
        carteDessiner(courant);
      }
    }, 4000);
  }
})();
</script>
</body>
</html>
