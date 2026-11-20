<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Conditions d’utilisation · Jalon</title>
<meta name="description" content="Conditions d’utilisation de Jalon : accès réservé aux comptes autorisés, interdiction de copier, de décompiler ou de réutiliser l’interface et le code.">
<meta name="robots" content="noindex">
@vite(['resources/css/global.css', 'resources/js/app.js'])
<style>
body{margin:0;background:var(--c-page);color:var(--c-ink)}
.cd{max-width:760px;margin:0 auto;padding:0 22px}
.cd-barre{position:sticky;top:0;z-index:10;backdrop-filter:blur(14px);background:color-mix(in srgb,var(--c-page) 82%,transparent);border-bottom:1px solid var(--c-line)}
.cd-nav{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0}
.cd-nav a:not(.btn){color:var(--c-ink-2);text-decoration:none;font-weight:500}
.cd-nav a:not(.btn):hover{color:var(--c-brand)}
.cd-main{padding:56px 0 72px}
.cd-eyebrow{color:var(--c-brand);font-weight:600;font-size:.9rem;letter-spacing:.06em;text-transform:uppercase;margin:0 0 12px}
.cd h1{font-size:clamp(2rem,5vw,3rem);letter-spacing:-.03em;line-height:1.08;margin:0 0 14px}
.cd-lead{color:var(--c-ink-2);font-size:1.08rem;line-height:1.6;margin:0 0 8px;max-width:60ch}
.cd-date{color:var(--c-muted);font-size:.9rem;margin:0 0 40px}
.cd h2{font-size:1.15rem;letter-spacing:-.01em;margin:36px 0 10px}
.cd p,.cd li{color:var(--c-ink-2);line-height:1.7}
.cd p{margin:0 0 14px}
.cd ul{margin:0 0 14px;padding-left:20px}
.cd li{margin:0 0 8px}
.cd-carte{background:var(--c-surface);border:1px solid var(--c-line);border-radius:18px;padding:26px}
.cd-carte p:last-child,.cd-carte ul:last-child{margin-bottom:0}
.cd-pied{border-top:1px solid var(--c-line);padding:22px 0 34px;color:var(--c-muted);font-size:.9rem;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.cd-pied a{color:var(--c-muted)}
.cd a:not(.btn){color:var(--c-brand)}
@media (max-width:640px){.cd-main{padding:36px 0 56px}.cd-carte{padding:20px}}
</style>
</head>
<body>
<div class="cd-barre"><div class="cd"><div class="cd-nav">
<a href="{{ url('/') }}" aria-label="Jalon, accueil"><x-logo :hauteur="30" /></a>
<nav class="vt-liens" aria-label="Navigation">
<a href="{{ url('/') }}#fonctions">Fonctions</a>
<a href="{{ route('connexion') }}">Connexion</a>
<x-bouton :href="route('inscription')" taille="petit">Créer mon espace</x-bouton>
</nav>
</div></div></div>

<main class="cd cd-main">
<p class="cd-eyebrow">Mentions légales</p>
<h1>Conditions d’utilisation</h1>
<p class="cd-lead">Jalon est un logiciel propriétaire de suivi des dossiers de transit et de dédouanement, édité à Lomé (Togo). L’accès au service vaut acceptation des présentes conditions.</p>
<p class="cd-date">Dernière mise à jour : 20 novembre 2026</p>

<div class="cd-carte">
<h2>1. Objet</h2>
<p>Les présentes conditions régissent l’accès et l’utilisation de l’application Jalon, de son site vitrine et de ses interfaces (ci-après « le Service »). Le Service est destiné aux transitaires, à leurs collaborateurs et à leurs clients disposant d’un compte autorisé.</p>

<h2>2. Accès réservé aux comptes autorisés</h2>
<p>L’accès aux espaces de travail est réservé aux personnes disposant d’un compte créé ou invité par Jalon ou par une agence autorisée. Les identifiants sont strictement personnels : ils ne doivent être ni partagés, ni cédés, ni utilisés par plusieurs personnes.</p>
<p>Toute tentative d’accès non autorisé, d’élévation de privilèges, d’extraction massive de données ou de contournement des mesures de sécurité est interdite et peut entraîner la suspension immédiate du compte.</p>

<h2>3. Propriété intellectuelle</h2>
<p>Le Service, son code source, son interface, ses composants graphiques, ses maquettes, ses textes et sa documentation sont la propriété exclusive de leur titulaire et sont protégés par le droit d’auteur.</p>
<p>Sauf autorisation écrite préalable, il est interdit de :</p>
<ul>
<li>copier, reproduire ou diffuser tout ou partie du Service ;</li>
<li>décompiler, désassembler ou pratiquer toute forme d’ingénierie inverse ;</li>
<li>réutiliser l’interface, les écrans ou le code dans un autre produit ou service ;</li>
<li>modifier, traduire ou créer des œuvres dérivées à partir du Service ;</li>
<li>revendre, louer, sous-licencier ou mettre le Service à disposition de tiers ;</li>
<li>retirer ou masquer les mentions de propriété et les filigranes.</li>
</ul>

<h2>4. Contenu et données</h2>
<p>Les données saisies (dossiers, clients, documents, factures) restent la propriété de l’agence qui les enregistre. Chaque agence n’accède qu’à ses propres données. Les documents sont conservés dans un espace de stockage privé, accessible uniquement après vérification des droits.</p>
<p>L’utilisateur s’engage à ne déposer que des documents dont il détient les droits et à respecter la réglementation applicable, notamment en matière de données personnelles.</p>

<h2>5. Disponibilité et responsabilité</h2>
<p>Le Service est fourni « en l’état ». Jalon s’efforce d’en assurer la disponibilité et la sécurité, sans garantie d’absence d’interruption ou d’erreur. Jalon ne saurait être tenu responsable des décisions douanières, des retards ou des pénalités résultant de l’usage du Service, ni des dommages indirects.</p>

<h2>6. Suspension</h2>
<p>Jalon peut suspendre ou résilier l’accès d’un compte en cas de violation des présentes conditions, d’usage frauduleux ou de non-paiement, sans préjudice de tout autre recours.</p>

<h2>7. Droit applicable</h2>
<p>Les présentes conditions sont soumises au droit togolais. Tout litige relève des juridictions compétentes de Lomé, sauf disposition impérative contraire.</p>

<h2>8. Contact</h2>
<p>Pour toute question relative à ces conditions : <a href="mailto:contact@jalon.tg">contact@jalon.tg</a>.</p>
</div>
</main>

<footer class="cd cd-pied">
<span>© Jalon, tous droits réservés</span>
<span><a href="{{ url('/') }}">Retour à l’accueil</a> · <a href="{{ route('suivi') }}">Suivre un dossier</a></span>
</footer>
</body>
</html>
