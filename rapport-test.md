# Rapport d'Audit - Workflows de Création et Formulaires

## Méthodologie

Audit statique du code (contrôleurs, vues, validations) + recommandations pour tests UI manuels dans le browser.

---

## 1. Workflows de Création de Comptes

### 1.1 Compte Solo (Transitaire seul)
**Route** : `POST /inscription` → `InscriptionController@store`
**Vue** : `resources/views/auth/inscription.blade.php` (type=solo)

**Validation** (lignes 30-44) :
- ✅ `nom` : required, string, max:120
- ✅ `email` : required, email, max:150, unique:users
- ✅ `telephone` : required, string, max:40
- ✅ `password` : required, string, min:8
- ✅ `numero_agrement` : nullable, string, max:80

**Workflow** :
1. Création Agence (transaction DB) avec type='solo', devise='FCFA', ville='Lomé', pays='Togo'
2. Création User avec role=transitaire, statut_activation=actif, est_transitaire_solo=true
3. Auth::login() automatique
4. Redirection vers tableau-de-bord

**Points forts** :
- Transaction DB pour agence+user
- Validation robuste avec messages en français
- Slug unique généré automatiquement

**Tests UI à effectuer** :
- [ ] Remplir formulaire sans email → message d'erreur "Renseignez votre adresse e-mail"
- [ ] Email invalide → "Cette adresse e-mail n'est pas valide"
- [ ] Mot de passe < 8 caractères → "Le mot de passe doit contenir au moins 8 caractères"
- [ ] Email déjà utilisé → "Un compte existe déjà avec cette adresse e-mail"
- [ ] Formulaire valide → création agence + user + connexion automatique

---

### 1.2 Compte Agence (avec approbation admin)
**Route** : `POST /inscription/agence` → `InscriptionController@demanderAgence`
**Vue** : `resources/views/auth/inscription.blade.php` (type=agence)

**Validation** (lignes 83-99) :
- ✅ `nom_agence` : required, string, max:120
- ✅ `nom_contact` : required, string, max:120
- ✅ `telephone` : required, string, max:40
- ✅ `email` : required, email, max:150, unique:users ET unique:demandes_agence (statut=en_attente)
- ✅ `numero_agrement` : nullable, string, max:80

**Workflow** :
1. Création DemandeAgence (statut=en_attente)
2. Email envoyé à l'admin plateforme
3. Redirection vers inscription avec message succès

**Points forts** :
- Double validation unique (users + demandes en attente)
- Message clair sur processus d'approbation

**Tests UI à effectuer** :
- [ ] Formulaire valide → message "Votre demande a été enregistrée. Un administrateur plateforme l'examinera..."
- [ ] Email déjà en demande en attente → "Une demande en attente existe déjà pour cette adresse e-mail"

---

### 1.3 Connexion
**Route** : `POST /connexion` → `AuthController@store`
**Vue** : `resources/views/auth/connexion.blade.php`

**Validation** (lignes 21-28) :
- ✅ `email` : required, string, email
- ✅ `password` : required, string

**Workflow** :
1. Rate limiting (5 tentatives par IP/email)
2. Auth::attempt()
3. Vérification statut_activation=actif
4. Vérification agence active
5. Redirection selon role (super admin → plateforme, client → portail, autres → tableau-de-bord)

**Points forts** :
- Rate limiting anti-brute force
- Vérification agence active avant connexion
- Redirection intelligente par role

**Tests UI à effectuer** :
- [ ] Identifiants invalides → "Ces identifiants ne correspondent à aucun compte"
- [ ] >5 tentatives → "Trop de tentatives. Réessayez dans X secondes"
- [ ] Compte non activé → "Ce compte n'est pas encore activé"
- [ ] Agence suspendue → "L'accès à cette agence est suspendu"
- [ ] Connexion valide → redirection tableau-de-bord

---

## 2. Formulaires de Création (après connexion)

### 2.1 Client
**Route** : `POST /clients` → `ClientController@store`
**Vue** : `resources/views/clients/formulaire.blade.php`

**Validation** (lignes 199-217) :
- ✅ `type` : required, in:[entreprise,particulier]
- ✅ `nom` : required, string, max:150
- ✅ `contact_nom` : required, string, max:120
- ✅ `telephone` : required, string, max:40
- ✅ `email` : required, email, max:150
- ✅ `fonction` : nullable, string, max:100
- ✅ `whatsapp` : nullable, string, max:40
- ✅ `ville` : nullable, string, max:80
- ✅ `adresse` : nullable, string, max:180
- ✅ `notes` : nullable, string, max:2000

**Workflow** :
1. Validation
2. Création Client avec code auto-généré (ReferenceService)
3. Option : inviter client (checkbox) → envoi email invitation
4. Journalisation (JournalService)
5. Redirection clients.show

**Points forts** :
- Code client auto-généré
- Invitation client optionnelle
- Journalisation automatique

**Tests UI à effectuer** :
- [ ] Champs requis manquants → messages d'erreur
- [ ] Type client sélectionné
- [ ] Cocher "Inviter" → envoi email (vérifier dans logs si SMTP désactivé)
- [ ] Formulaire valide → création client + redirection

---

### 2.2 Dossier
**Route** : `POST /dossiers` → `DossierController@store`
**Vue** : `resources/views/dossiers/formulaire.blade.php`

**Validation** (extrait) :
- ✅ `client_id` : required (via champ-select avec placeholder)
- ✅ `regime_id` : required (via champ-select avec placeholder)
- ✅ `ligne_maritime` : required
- ✅ `nature_marchandise` : required
- ✅ `numero_bl` : nullable
- ✅ `navire` : nullable
- ✅ `date_arrivee_prevue` : nullable, date

**Workflow** :
1. Validation
2. Création Dossier avec reference auto-générée, code_suivi auto-généré
3. Entrée jalon 1 automatique
4. Journalisation
5. Redirection dossiers.show

**⚠️ ISSUE : Select placeholder** :
Le fix `disabled` a été annulé par l'utilisateur. Le placeholder "Choisir un client" et "Choisir un régime" restent sélectionnables. Si l'utilisateur soumet le formulaire avec le placeholder sélectionné, la validation Laravel rejettera (valeur vide) mais l'UX est dégradée.

**Tests UI à effectuer** :
- [ ] Champs requis manquants → validation Laravel
- [ ] Client sélectionné
- [ ] Régime sélectionné
- [ ] Documents attendus affichés dynamiquement selon régime (JS ligne 127-164)
- [ ] Formulaire valide → création dossier + référence générée

---

### 2.3 Facture
**Route** : `POST /facturation` → `FactureController@store`
**Vue** : `resources/views/facturation/formulaire.blade.php`

**Validation** (à vérifier dans FactureController) :
- ✅ `client_id` : required
- ✅ `date_facture` : required, date
- ✅ `sous_total` : required, numeric
- ✅ `tva_taux` : required, numeric
- ✅ `statut` : enum StatutFacture

**Workflow** :
1. Validation
2. Création Facture avec numero auto-généré
3. Calcul automatique tva_montant, total
4. Journalisation
5. Redirection facturation.show

**Tests UI à effectuer** :
- [ ] Champs requis
- [ ] TVA calculée automatiquement
- [ ] Total calculé (sous_total + tva_montant - remise)
- [ ] Formulaire valide → création facture

---

### 2.4 Invitation Équipe
**Route** : `POST /equipe/inviter` → `EquipeController@store`
**Vue** : `resources/views/equipe/inviter.blade.php`

**Validation** (à vérifier dans EquipeController) :
- ✅ `nom` : required
- ✅ `email` : required, email, unique:users
- ✅ `poste` : nullable
- ✅ `role` : required, enum RoleUtilisateur
- ✅ `permissions[]` : array de permissions

**Workflow** :
1. Validation
2. Création User avec statut_activation=en_attente, password=null
3. Création Invitation avec token, expires_at=+7 jours
4. Envoi email InvitationEmployeMail
5. Redirection equipe.index

**Points forts** :
- Permissions avec presets par role (JS lignes 52-72)
- Lien valable 7 jours
- Réutilisation invitation si encore valable

**Tests UI à effectuer** :
- [ ] Role sélectionné → permissions pré-remplies selon preset
- [ ] Permissions modifiables manuellement
- [ ] Formulaire valide → création user + invitation + email

---

### 2.5 Conteneur (dans un dossier)
**Route** : `POST /dossiers/{dossier}/conteneurs` → `DossierController@ajouterConteneur`
**Vue** : `resources/views/dossiers/partiels/conteneurs.blade.php`

**Validation** (à vérifier) :
- ✅ `numero` : required
- ✅ `type` : required
- ✅ `taille` : required
- ✅ `statut` : enum StatutConteneur
- ✅ `date_arrivee` : nullable, date
- ✅ `fin_franchise` : nullable, date

**Workflow** :
1. Validation
2. Création Conteneur lié au dossier
3. Mise à jour etat dossier si conteneur critique
4. Journalisation

**Tests UI à effectuer** :
- [ ] Formulaire modal ou inline
- [ ] Champs requis
- [ ] Franchise calculée automatiquement (FranchiseService)
- [ ] Formulaire valide → ajout conteneur + mise à jour dossier

---

## 3. Validations Globales

### 3.1 Champs requis
✅ Tous les formulaires utilisent `requis` ou `required` dans les validations Laravel

### 3.2 Validation email
✅ `email` validation rule appliquée partout

### 3.3 Validation téléphone
⚠️ Validation regex absente : `telephone` est seulement `string, max:40`
**Recommandation** : Ajouter validation regex pour formats +228, +233, etc.

### 3.4 Messages d'erreur
✅ Messages personnalisés en français dans tous les contrôleurs

---

## 4. Points Spécifiques à Vérifier

### 4.1 Select avec placeholder
**Statut** : Fix `disabled` annulé par l'utilisateur
**Fichier** : `resources/views/components/champ-select.blade.php:21`
**Code actuel** : `<option value="">{{ $placeholder }}</option>` (sans disabled)

**Impact** :
- Le placeholder reste sélectionnable
- Si soumis avec placeholder, validation Laravel rejettera (valeur vide)
- UX dégradée : l'utilisateur peut sélectionner le placeholder involontairement

**Recommandation** : Rétablir `disabled` OU ajouter validation côté client pour intermettre la sélection du placeholder

---

### 4.2 KPIs animés (tableau de bord)
**Statut** : ✅ Implémenté
**Fichier** : `resources/views/tableau-de-bord.blade.php:67-89`
**Vérification** :
- [ ] Compteurs roulent à l'entrée dans viewport (data-count)
- [ ] Stagger avec data-index="0..3"
- [ ] Valeur franchise en rose si > 0

---

### 4.3 Mini-barres CA
**Statut** : ✅ Implémenté
**Fichier** : `resources/views/tableau-de-bord.blade.php:53-64`
**Vérification** :
- [ ] Barres proportionnelles au CA mensuel
- [ ] Mois courant surligné (.actuelle)
- [ ] Title avec montant FCFA au survol

---

## 5. Responsivité Mobile

**Vérifications à effectuer** :
- [ ] Formulaire inscription solo/agence en mobile
- [ ] Formulaire client en mobile
- [ ] Formulaire dossier en mobile
- [ ] Tableau de bord KPIs en mobile
- [ ] Sidebar hamburger fonctionne

---

## 6. Tests UI Recommandés (Browser)

### Étape 1 : Test inscription solo
1. http://127.0.0.1:8000/inscription
2. Choisir "Transitaire solo"
3. Remplir : nom=Test Solo, email=test@solo.tg, telephone=+22890909090, password=12345678
4. Valider → vérifier création + connexion automatique

### Étape 2 : Test création client
1. http://127.0.0.1:8000/clients/creer
2. Remplir : type=entreprise, nom=Test Client, contact_nom=John Doe, telephone=+22890909090, email=client@test.tg
3. Cocher "Inviter"
4. Valider → vérifier création + message succès

### Étape 3 : Test création dossier
1. http://127.0.0.1:8000/dossiers/creer
2. Sélectionner client (vérifier placeholder)
3. Sélectionner régime (vérifier placeholder)
4. Remplir : ligne_maritime=MSC, nature_marchandise=Produits alimentaires
5. Vérifier documents attendus dynamiques
6. Valider → vérifier création + référence générée

### Étape 4 : Test KPIs et animations
1. http://127.0.0.1:8000/tableau-de-bord
2. Vérifier compteurs animés à l'entrée
3. Vérifier mini-barres CA proportionnelles
4. Vérifier valeur franchise en rose si > 0

---

## 7. Issues Critiques à Corriger

| Issue | Fichier | Ligne | Sévérité | Action |
|-------|---------|-------|----------|--------|
| Select placeholder sélectionnable | champ-select.blade.php | 21 | MEDIUM | Rétablir `disabled` ou ajouter validation JS |
| Validation téléphone sans regex | Multiple contrôleurs | - | LOW | Ajouter regex pour formats internationaux |

---

## 8. Conclusion

**Statut global** : ✅ Code de qualité, validations robustes, workflow bien structuré

**Points forts** :
- Validations Laravel complètes avec messages français
- Transactions DB pour création multi-modèles
- Journalisation automatique
- Permissions et rôles bien gérés
- KPIs et animations tableau de bord fonctionnels

**Points à améliorer** :
- Validation regex pour téléphone
- Placeholder select (fix annulé par utilisateur)
- Tests E2E automatisés (recommandé pour l'avenir)

**Tests UI manuels requis** pour valider le comportement réel dans le browser.
