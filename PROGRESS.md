# Jalon — état d'avancement

ERP de suivi de transit et de douane pour une agence portuaire de Lomé.
Laravel 12 · Blade · Livewire 4 · Tailwind CSS 4 · MySQL · interface 100 % française.

---

## Lot 0 — Socle technique et design system ✅

### Base de données

- Base MySQL `jalon` (voir `.env`), 25 migrations exécutées dans l'ordre de dépendance
  (`agences` avant `users`, FK `users.client_id` ajoutée dans une migration tardive).
- Tables : `agences`, `users`, `cache`, `jobs`, `regimes`, `jalons_config`, `clients`,
  `dossiers`, `historique_statuts`, `documents`, `conteneurs`, `couts`, `factures`,
  `facture_lignes`, `paiements`, `regimes_franchise`, `documents_requis_par_regime`,
  `alertes`, `notifications`, `journal_activite`, `parametres_agence`, `invitations`,
  `evenements_conteneur` (ajout nécessaire pour l'historique du détail conteneur).
- `php artisan migrate:fresh --seed` passe sans erreur.

### Code applicatif

- 8 énumérations (`app/Enums`) : rôles, statuts de dossier, factures, conteneurs,
  échéances J-2/J-1/J0, gravités et types d'alerte, modes de paiement.
- 21 modèles Eloquent avec relations, casts et accesseurs métier
  (état d'un dossier, jours de franchise, marge réelle, code de suivi…).
- 5 services : `ReferenceService` (références JL-/FA-/CL- pilotées par les paramètres
  d'agence et codes de suivi K7M2-9QX4), `FranchiseService` (franchise par régime et
  ligne maritime), `AlerteService` (génération idempotente J-2/J-1/J0 + notifications),
  `FactureService` (totaux, TVA, statut), `JournalService`.
- Authentification par session : connexion, déconnexion, limitation des tentatives,
  refus des comptes non activés, redirection du portail client.

### Données de démonstration

- 1 agence, 4 comptes internes + 3 comptes portail, 18 clients, 9 jalons, régimes et
  franchises, 24 dossiers `JL-2027-0011` → `0034`, 23 conteneurs (17 au port),
  12 factures `FA-2027-0001` → `0012` avec lignes et paiements, 9 alertes dont
  3 non lues, journal d'activité.
- Chiffres conformes aux maquettes : CA du mois **9 120 000 FCFA** (+12,4 % vs mois
  précédent), marges 150 000 / 305 000 / 268 000 / 390 000 FCFA sur les dossiers phares,
  JL-2027-0031 en retard (J0), TGHU 8830214 à J-1, CMAU 2276405 à J-2.
- Documents PDF fictifs générés avec Dompdf.

### Design system

- Jetons de couleurs et de rayons dans `resources/css/app.css` (thème clair de référence
  `#EEEAE7` / `#C9460E`, thème sombre complet), bascule clair/sombre persistante.
- Police Instrument Sans embarquée (`resources/fonts`, aucun appel réseau).
- Composants Blade : `bouton`, `badge`, `carte`, `champ`, `champ-select`, `champ-zone`,
  `modale` (Échap, clic sur le fond, focus rendu), `etat-vide`, `jalons`, `toasts`, `icone`.
- Layout `layouts.app` : barre latérale 270→300 px, pilule active noire, compteurs,
  profil et déconnexion, menu mobile, recherche, actions de page.
- Page de connexion et tableau de bord (4 zones : CA, graphiques Chart.js, échéances et
  alertes, derniers dossiers et journal) fidèles aux maquettes.

### Vérifications effectuées

- `php artisan migrate:fresh --seed` : OK, compteurs relus en base.
- `npm run build` : OK (CSS 66,8 kB, JS 207,8 kB incluant Chart.js).
- `php artisan view:cache` : aucune erreur Blade.
- Toutes les routes répondent 200 une fois connecté ; `/` redirige correctement.
- Connexion testée pour `admin`, `comptable`, `agent` et `client` ; le client est envoyé
  vers le portail, les autres vers le tableau de bord.
- Navigation filtrée par rôle côté serveur (comptable : pas d'Équipe/Paramètres/Conteneurs ;
  transitaire : pas de Facturation/Paramètres).
- Rendu vérifié dans le navigateur en clair et en sombre, en largeur mobile et desktop
  (correction d'un chevauchement de grille dû au voile mobile).

### Écarts assumés par rapport aux maquettes

1. **Graphiques** : les barres utilisent les données réelles du jeu de démonstration, donc
   des hauteurs plus modestes que les 14 à 21 dossiers/mois des maquettes. Le CA et la marge
   sont exprimés en millions de FCFA comme sur la maquette.
2. **Badge « Dossiers »** : compte tous les dossiers de l'agence (24, comme la maquette),
   et non seulement ceux qui ne sont pas livrés (20).
3. **Recherche globale** : un seul champ dans l'en-tête, qui interroge la section courante
   (dossiers, conteneurs, clients ou facturation) au lieu d'être dupliqué dans chaque page.
4. **Troisième échéance** : la maquette mélange une franchise de conteneur et une échéance
   de facture ; la carte affiche les trois prochaines franchises puis la facture impayée la
   plus proche.
5. **Alertes** : 9 alertes en base (5 franchises J-2/J-1/J0 + 4 alertes métier), dont 3 non
   lues comme sur la maquette.
6. **Facture PDF** : la version imprimable s'ouvre dans le navigateur avec le bouton
   « Imprimer / Enregistrer en PDF » (pas de moteur PDF côté serveur).
7. **Paramètres** : les listes de régimes, de pièces requises et de jalons sont affichées en
   lecture seule ; leur édition en base n'est pas encore exposée.

### Incohérences relevées dans les maquettes

- « Komia Distribution » (tableau de bord, facturation) vs « Komla Distribution » (tableau
  de bord, échéances) — retenu : **Komia Distribution**.
- « Alertes » vs « Alertes & Dépassements » selon les écrans.
- Document manquant rattaché à JL-2027-0031 (écran Alertes) ou JL-2027-0034 (tableau de bord).
- Barre latérale dessinée avec ou sans icônes, logo et bouton Déconnexion selon les écrans.
- Statuts de facture « Émise » vs « Impayée ».
- Conteneur CMAU 2276405 rattaché à JL-2027-0027 ou à JL-2027-0034/JL-2027-0020.
- Marge affichée sur des dossiers sans facture (marge dite « estimée » dans le code).
- Variation du CA « +12,4 % par rapport à septembre » alors que la maquette est datée
  d'octobre ; le libellé est calculé à partir de la période sélectionnée.

---

## Lot 1 — Comptes, équipe et profil ✅

- **Équipe** (`/equipe`) : tableau des collaborateurs, rôle modifiable en ligne, activation/désactivation,
  invitations en attente avec renvoi, annulation et lien d'activation copiable.
- **Inviter un employé** (`/equipe/inviter`) : formulaire conforme à la maquette (profil + permissions
  détaillées en 7 bascules), pré-remplissage des permissions selon le rôle choisi, envoi d'un e-mail
  valable 7 jours, refus des doublons d'adresse.
- **Activation** (`/invitation/{token}`) : choix du mot de passe, activation du compte, connexion
  automatique, gestion des invitations expirées ou déjà utilisées.
- **Mot de passe oublié** (`/mot-de-passe-oublie`) : envoi d'un lien signé valable 60 minutes,
  formulaire de réinitialisation, invalidation du jeton après usage.
- **Mon profil** (`/profil`) : coordonnées, photo de profil (JPG/PNG 2 Mo max.), changement de mot
  de passe avec vérification de l'ancien, préférences (thème clair/sombre appliqué immédiatement,
  notifications e-mail, langue verrouillée sur le français).
- **Permissions** : nouvelle colonne `users.permissions` (JSON), droits par défaut selon le rôle,
  contrôlées côté serveur (`abort_unless`) en plus des rôles.
- **Français** : fichiers `lang/fr/validation.php` et `lang/fr/passwords.php` ajoutés.
- Barre latérale enrichie (marque Jalon, bloc agence, icônes, Paramètres en pied de page) et fil
  d'Ariane sur les pages secondaires.

### Vérifications

- Invitation → e-mail (log) → activation → connexion testées de bout en bout : compte passé à
  `actif`, redirection vers le tableau de bord, invitation marquée acceptée.
- Comptable et transitaire reçoivent bien 403 sur `/equipe` ; les liens de navigation sont filtrés
  par rôle.

---

## Lot 2 — Dossiers ✅

- **Liste** (`/dossiers`) : recherche (référence, client, B/L, code de suivi, conteneur), filtres
  état / période / responsable, tableau à 9 colonnes (franchise J0/J-1/J-2, état calculé, marge
  réelle uniquement si facturé), pagination 7 par page, actions Consulter / Modifier / Archiver.
- **Création et modification** (`/dossiers/creer`, `/dossiers/{reference}/modifier`) : formulaire
  conforme à la maquette (client, régime, référence générée, ligne, B/L, nature, pays d'origine,
  navire, arrivée prévue), liste dynamique des pièces attendues selon le régime, création en
  brouillon, génération de la référence `JL-2027-xxxx` et du code de suivi.
- **Fiche dossier** (`/dossiers/{reference}`) : en-tête, 7 onglets, aperçu (marge prévisionnelle,
  marge réelle, facturation, documents, 9 jalons, alerte franchise, statut d'avancement, données
  générales, copie du code et du lien de suivi).
- **Passage de jalon** : modale avec condition de passage, date et commentaire, écriture dans
  l'historique, clôture du dossier au jalon 9 et sortie des conteneurs ; recul de jalon réservé à
  l'administrateur avec motif.
- **Documents** : téléversement (PDF/JPG/PNG 5 Mo), validation, suppression, téléchargement,
  visibilité portail, contrôle des pièces exigées par le régime.
- **Coûts** : saisie, suppression, totaux et marge réelle.
- **Conteneurs** : liste avec franchise, événements et ajout d'un conteneur (franchise calculée).
- **Facture** : consultation des factures et du détail des lignes (émission au lot 5).
- **Alertes** : alertes rattachées au dossier.
- Les URL utilisent la référence du dossier (`/dossiers/JL-2027-0031`).

### Vérifications

- Création d'un dossier test (`JL-2027-0035`), passage au jalon 2, ajout d'un coût et d'un document,
  téléchargement vérifié, puis base réinitialisée.
- Tous les onglets répondent 200 ; comptable bloqué à la création (403) ; transitaire autorisé.
- Rendu vérifié en mobile : tableau à défilement horizontal, modale de jalon, correction d'un
  débordement horizontal de la grille.

---

## Lot 3 — Conteneurs ✅

- **Liste** (`/conteneurs`) : pastilles Tous / Au port / Sortis / Restitués avec compteurs,
  recherche globale (conteneur, dossier ou client), tableau Numéro / Dossier / Type / Statut /
  Fin de franchise / Alerte, tri des conteneurs au port par échéance, pagination 10.
- **Détail** (`/conteneurs/{numéro}`) : bandeau de franchise (J0 rouge, J-1 ambre, J-2 jaune),
  informations techniques (dossier, client, caractéristiques, ligne, franchise applicable,
  arrivée, date limite, sortie/restitution), mise à jour du statut et traçabilité portuaire.
- Chaque changement de statut crée un événement daté et une entrée dans le journal d'activité ;
  les dates de sortie et de restitution sont renseignées automatiquement.
- Accès réservé aux rôles administrateur et transitaire, contrôle d'agence sur chaque fiche.

### Vérifications

- Liste et fiche rendues en 200, filtre par statut et recherche « MSCU » testés.
- Mise à jour du statut vérifiée en base (au_port → sorti) puis jeu de données réinitialisé.

---

## Lot 4 — Alertes ✅

- **Centre d'alertes** (`/alertes`) : filtres Toutes / Non lues / Critiques avec compteurs,
  cartes colorées par gravité (critique, haute, moyenne), badges Non lue / Lue et gravité,
  action Ouvrir (fiche conteneur ou dossier), bascule lu / non lu, pagination 8.
- **Tout marquer comme lu** en une action.
- **Génération planifiée** : commande `php artisan jalon:alertes` (service existant, clé unique
  `conteneur_id + écheance` donc aucun doublon), planifiée chaque matin à 07:00 dans
  `routes/console.php`.
- Accès administrateur, transitaire et comptable ; contrôle d'agence sur chaque alerte.

### Vérifications

- Les trois filtres répondent 200 ; compteurs cohérents (9 alertes, 3 non lues, 1 critique).
- Marquage lu puis non lu vérifiés en base ; `jalon:alertes` retourne 0 alerte (aucun doublon
  après réexécution) ; `schedule:list` affiche la tâche quotidienne.

---

## Lot 5 — Clients et facturation ✅

### Clients

- **Répertoire** (`/clients`) : recherche (nom, code, contact, e-mail, téléphone), tableau
  Client / Type / Contact / Dossiers, panneau de sélection avec compte portail, dossiers,
  solde dû, coordonnées, dernier dossier et actions rapides.
- **Nouveau / modifier** (`/clients/creer`, `/clients/{code}/modifier`) : informations
  générales, coordonnées, notes internes, case « envoyer une invitation d'accès à l'espace
  client » (e-mail avec lien valable 7 jours, compte `client` en attente d'activation).
- **Fiche** (`/clients/{code}`) : dossiers, facturé, solde dû, coordonnées, historique des
  dossiers (jalon, état, marge), factures et paiements, état de l'accès au portail, archivage
  (suppression douce) et renvoi d'invitation.
- Code client généré par `ReferenceService` (`CLI-2027-001`), URL par code.
- Colonne `clients.fonction` ajoutée (migration additive) pour la fonction du contact.
- Depuis une fiche client, « Nouveau dossier pour ce client » pré-sélectionne le client dans
  le formulaire de dossier (`?client=CLI-…`).

### Facturation

- **Liste** (`/facturation`) : filtres Toutes / Impayées / Partielles / Payées avec compteurs,
  recherche (numéro, client, dossier), tableau N° / Dossier / Client / État / Échéance et
  panneau de détail (lignes, HT, remise, TVA, TTC, déjà réglé, reste à payer).
- **Nouvelle facture** (`/facturation/creer`) : client, dossier filtré selon le client, numéro
  généré, taxe 18 % ou exonérée, dates, lignes dynamiques (ajout/suppression, totaux calculés
  en direct), remise, notes, émission.
- **Paiement** : modale (montant, mode, date, référence) avec contrôle du solde restant et
  recalcul automatique du statut (impayée / partielle / payée) via `FactureService`.
- **Fiche facture** (`/facturation/{numéro}`) : détail, encaissements et suivi (émission,
  échéance, émetteur, statut).
- **Version imprimable** (`/facturation/{numéro}/imprimable`) : document prêt à imprimer ou à
  enregistrer en PDF depuis le navigateur (aucune dépendance PDF côté serveur).

### Vérifications

- Création d'un client avec invitation : compte `client` en attente et invitation créés, e-mail
  journalisé ; fiche, liste et formulaire en 200.
- Facture de test émise (`FA-2027-0013`, 436 600 FCFA TTC pour 370 000 HT) puis paiement de
  200 000 FCFA : statut passé à « partiellement payée », solde 236 600 FCFA.
- Facture existante, création et version imprimable en 200 ; jeu de démonstration réinitialisé.

---

## Lot 6 — Exports et rapports ✅

- **Rapports** (`/exports`) : période 7 j / 30 j / 90 j / 12 mois / tout, indicateurs
  (chiffre d'affaires facturé, encaissé, reste à recouvrer, dossiers, conteneurs au port).
- **Exports CSV** (séparateur « ; », BOM UTF-8 pour Excel) : dossiers, factures, clients,
  conteneurs et journal d'activité (2 000 dernières entrées).
- **Journal d'activité** : 12 dernières actions avec date, action, détail et utilisateur.
- Accès réservé aux rôles administrateur et comptable.

### Vérifications

- `/exports` et `/exports?periode=365` en 200 ; les cinq fichiers CSV téléchargés en
  `text/csv` (dossiers : 24 lignes + en-tête, accents et montants corrects).
- Transitaire : 403 sur `/exports` et `/parametres`.

---

## Lot 7 — Paramètres de l'agence ✅

- **Menu de configuration** (`/parametres?section=…`) : 8 sections.
- **Informations de l'agence** : logo (PNG/JPG/WebP, 2 Mo), nom, agrément en douane, e-mail,
  téléphone, adresse, ville, pays.
- **Paramètres de transit** : devise, franchise par défaut, préfixe et année des dossiers,
  aperçu du prochain numéro.
- **Documents et formalités** : pièces exigées par régime douanier (lecture seule).
- **Workflow et jalons** : les 9 étapes avec condition de passage et visibilité client.
- **Notifications et alertes** : seuils J-2/J-1/J0, canaux application et e-mail, heure d'envoi.
- **Facturation** : TVA, préfixe et année des factures, délai de paiement, mention TVA et pied
  de page ; aperçu du prochain numéro.
- **Sécurité et accès** : longueur minimale du mot de passe, validité des invitations (utilisée
  par les invitations d'équipe et clients), déconnexion après inactivité.
- **Intégrations** : état des canaux e-mail, WhatsApp et SMS (sans bouton mort).
- Chaque enregistrement est tracé dans le journal d'activité ; accès réservé aux administrateurs.

### Vérifications

- Les huit sections répondent 200 ; enregistrement de la franchise par défaut testé (5 puis
  retour à 4) ; transitaire refusé (403).

---

## Lot 8 — Portail client et suivi public ✅

- **Espace client** (`/portail`) : tableau de bord « Bonjour, … », une carte par dossier avec
  référence, étape en cours, badge (franchise J0 / en cours / terminé) et barre de progression.
- **Détail** (`/portail/dossiers/{référence}`) : progression globale (x / 9 jalons), prochaine
  étape, documents partagés (téléchargement contrôlé, `visible_client` obligatoire) et factures
  (total, payé, solde) — lecture seule, aucune action d'écriture.
- **Suivi public** (`/suivi`, `/suivi/{code}`) : recherche par code de suivi, étape, franchise,
  régime, arrivée prévue et conteneurs, sans aucune donnée confidentielle.
- **Mise en page dédiée** (`layouts.portail`) : carte centrée, thème clair/sombre, déconnexion,
  responsive mobile (plein écran sous 480 px).
- **Sécurité** : un client n'accède qu'à ses propres dossiers (404 sinon), le téléchargement
  vérifie à la fois le dossier, le document et la visibilité portail ; un utilisateur interne
  reçoit 403 sur `/portail`.

### Vérifications

- Connexion `client@jalon.tg` → redirection automatique vers `/portail` ; dossier
  `JL-2027-0031` en 200, dossier d'un autre client en 404 ; document téléchargé en
  `application/pdf`, document d'un autre dossier en 404 ; `/suivi/{code}` en 200.

---

## Lot 9 — Identité visuelle et inscription

- **Logos officiels** : `assets/` conservé comme source, déclinaisons copiées en
  `public/img/logo-jalon-clair.png` et `logo-jalon-sombre.png` ; composant
  `<x-logo>` qui bascule automatiquement entre les deux selon le thème
  (barre latérale, en-tête du portail, pages d'authentification).
- **Reliefs adoucis** : ombres néomorphiques très courtes (`--ombre-relief`,
  `--ombre-creux`), bordures de champs `#8a817a`, panneau latéral sur `--c-surface`
  pour retrouver la séparation de la maquette, `segmented` en rectangle arrondi
  (12 px / 7 px) comme sur les écrans de référence.
- **Inscription** (`/inscription`, lien « Créer mon espace » depuis la connexion) :
  sélecteur « Vous êtes… » transitaire solo / agence, formulaire nom, e-mail,
  téléphone, mot de passe, n° d'agrément facultatif ; la création ouvre une agence
  `solo`, un utilisateur `transitaire` actif (`est_transitaire_solo = true`) et
  connecte directement.
- **Inscription agence** (`/inscription?vous=agence`) : nom d'agence, e-mail
  professionnel, encart bleu d'information et bouton « Contacter le support » qui
  transmet la demande (message de confirmation, aucune création de compte).
- **Facturation** : le transitaire solo y accède en écriture (liste, création,
  encaissement, imprimable) via `peutFacturer()` ; un transitaire rattaché à une
  agence reste refusé (403), comme les exports.
- **Paramètres** : le transitaire solo y accède en écriture (identité de son agence,
  transit, facturation, alertes, sécurité) via `peutGererParametres()`.
- **Transitaire solo** : le menu « Équipe » est visible et la page est consultable
  en lecture seule (bandeau « Consultation seule », aucun bouton d'invitation,
  rôle et statut non modifiables) ; la gestion reste réservée aux agences, toutes
  les routes d'écriture renvoyant 403.
- **Vérifications** : `/connexion`, `/inscription`, `/inscription?vous=agence`,
  `/tableau-de-bord`, `/suivi/{code}`, thème clair et sombre ; inscription test
  réelle (302 vers le tableau de bord puis 200), compte de test supprimé ensuite ;
  `php artisan view:cache` et `npm run build` passent.

---

---

## Commandes

```bash
php artisan migrate:fresh --seed   # base de démonstration
php artisan storage:link           # photos de profil servies depuis /storage
npm run build                      # assets de production
php artisan serve                  # http://127.0.0.1:8000
```

Comptes de démonstration (mot de passe `password`) :

| Compte | Rôle |
| --- | --- |
| `admin@jalon.tg` | Administrateur |
| `agent@jalon.tg` | Transitaire |
| `comptable@jalon.tg` | Comptable |
| `client@jalon.tg` | Client (portail) |

## Correctifs du 11 novembre 2026

- **Facturation** : le client et le dossier facturés sont vérifiés contre l'agence
  courante, et le dossier doit appartenir au client choisi ; en cas d'écart, la
  facture n'est pas créée et un message français explique pourquoi.
- **Conteneurs** : un retour en arrière (« au port », « sorti ») efface les dates
  de sortie et de restitution devenues fausses au regard du statut.
- **Références** : l'unicité des codes clients, références de dossier et numéros
  de facture devient par agence (index composite) au lieu d'un index global ; le
  verrou pessimiste sur l'agence suffit alors à écarter deux créations simultanées.
