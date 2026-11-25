# Plan d'implémentation - Amélioration espace super admin

## Ajout 1 : Historique des demandes (onglets En attente/Traitées)

### Fichiers à modifier
- `app/Http/Controllers/AdministrationPlateformeController.php` - méthode index()
- `resources/views/plateforme/index.blade.php` - section demandes

### Modifications
1. **Controller index()** :
   - Ajouter paramètre `$onglet = $request->query('onglet', 'attente')`
   - Si `$onglet === 'traitees'` : charger demandes where('statut', '!=', 'en_attente') → orderBy('traitee_at', 'desc') → paginate(10, ['*'], 'traitees_page')
   - Si `$onglet === 'attente'` : garder la logique existante (statut en_attente, paginate avec 'demandes_page')
   - Passer `$onglet` à la vue

2. **Vue plateforme/index.blade.php** :
   - Ajouter navigation par onglets : deux liens/segments au-dessus de la section demandes
   - Les liens : `/plateforme?onglet=attente` et `/plateforme?onglet=traitees`
   - Si onglet traitees : afficher colonnes supplémentaires : statut (badge vert/rouge), motif de refus, nom du traiteur, date de traitement
   - Garder pagination indépendante (demandes_page pour attente, traitees_page pour traitees)

### Tests à ajouter (DemandesAgenceTest.php)
- test_onglet_traitees_affiche_demandes_approuvees_et_refusees
- test_onglet_traitees_naffiche_pas_demandes_en_attente

---

## Ajout 2 : Notes et contact sur les demandes

### Fichiers à modifier
- Migration : `database/migrations/xxxx_xx_xx_add_notes_to_demandes_agence.php`
- `app/Http/Controllers/AdministrationPlateformeController.php` - méthode enregistrerNotes()
- `routes/web.php` - nouvelle route POST /plateforme/demandes/{demande}/notes
- `resources/views/plateforme/index.blade.php` - bouton Notes + modale + liens WhatsApp/E-mail
- `app/Models/DemandeAgence.php` - ajouter `notes` et `dernier_contact_at` au fillable/casts

### Migration
```php
$table->text('notes')->nullable();
$table->timestamp('dernier_contact_at')->nullable();
```

### Modifications
1. **Controller enregistrerNotes()** :
   - Validation : notes max 2000, dernier_contact_at date nullable, not future
   - Update sur la demande (fonctionne en attente ET traitées)
   - Retour back() with success

2. **Vue** :
   - Pour chaque demande : bouton "Notes" ouvre x-modale
   - Dans la modale : formulaire avec champ notes (textarea max 2000) et champ date dernier_contact_at
   - Liens : WhatsApp (wa.me/{numéro nettoyé}, rel="noopener", target="_blank"), E-mail (mailto:)
   - Afficher "Dernier contact : jj/mm/aaaa" sous le nom si date existe

### Tests à ajouter
- test_super_admin_peut_enregistrer_notes_et_dernier_contact
- test_utilisateur_normal_recoit_403_sur_notes
- test_lien_whatsapp_avec_prefixe_228_si_8_chiffres

---

## Ajout 3 : Suspension avec confirmation et motif

### Fichiers à modifier
- Migration : `database/migrations/xxxx_xx_xx_add_suspension_to_agences.php`
- `app/Http/Controllers/AdministrationPlateformeController.php` - méthode changerStatutAgence()
- `resources/views/plateforme/index.blade.php` - bouton suspension ouvre x-modale
- `app/Models/Agence.php` - ajouter `motif_suspension` et `suspendue_at` au fillable/casts

### Migration
```php
$table->string('motif_suspension', 300)->nullable();
$table->timestamp('suspendue_at')->nullable();
```

### Modifications
1. **Controller changerStatutAgence()** :
   - Si suspension : validation motif required, 3-300 caractères
   - Si réactivation : motif nullable
   - Vérifier que le super admin ne suspend pas sa propre agence (si user->agence_id === agence->id, refuser)
   - Dans transaction : update agence + enregistrer journal (ajout 4)
   - Si réactivation : vider motif_suspension et suspendue_at

2. **Vue** :
   - Remplacer le bouton direct par un bouton qui ouvre x-modale
   - Modale suspension : texte d'avertissement + champ motif obligatoire
   - Modale réactivation : confirmation simple + motif facultatif
   - Afficher motif de suspension sous le nom de l'agence suspendue

### Tests à ajouter
- test_suspension_sans_motif_echoue_validation
- test_suspension_avec_motif_reussit_et_stocke_motif
- test_reactivation_efface_motif_suspension
- test_super_admin_ne_peut_pas_suspender_sa_propre_agence

---

## Ajout 4 : Journal des actions de la plateforme

### Fichiers à créer/modifier
- Migration : `database/migrations/xxxx_xx_xx_create_journal_plateforme_table.php`
- `app/Models/JournalPlateforme.php` - nouveau modèle
- `app/Services/JournalPlateformeService.php` - nouveau service ou méthode statique
- `app/Http/Controllers/AdministrationPlateformeController.php` - méthode journal()
- `routes/web.php` - nouvelle route GET /plateforme/journal
- `resources/views/plateforme/journal.blade.php` - nouvelle vue
- `resources/views/plateforme/index.blade.php` - lien "Journal" dans la page

### Migration
```php
$table->id();
$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
$table->string('action', 60);
$table->string('cible_type', 40)->nullable();
$table->unsignedBigInteger('cible_id')->nullable();
$table->string('cible_libelle', 150)->nullable();
$table->text('details')->nullable();
$table->timestamps();
$table->index('created_at');
```

### Modifications
1. **Modèle JournalPlateforme** :
   - fillable : user_id, action, cible_type, cible_id, cible_libelle, details
   - relationship : user() -> belongsTo(User)

2. **Service/Static method enregistrer()** :
   - Signature : `enregistrer($action, $cible = null, $details = null)`
   - Crée une ligne avec user_id = auth()->id(), timestamp now
   - Stocke le motif dans details pour suspension

3. **Intégration dans Controller** :
   - approuver() : enregistrer('demande_approuvee', $demande) dans la transaction
   - refuser() : enregistrer('demande_refusee', $demande, $motif) dans la transaction
   - changerStatutAgence() : enregistrer('agence_suspendue'/'agence_reactivee', $agence, $motif) dans la transaction
   - enregistrerNotes() : enregistrer('notes_modifiees', $demande)

4. **Vue journal.blade.php** :
   - Tableau paginé (20 par ligne)
   - Colonnes : date/heure, auteur, action (en français), cible, détails
   - Layout extends plateforme

5. **Vue index.blade.php** :
   - Ajouter lien "Journal" dans la page (probablement dans le header ou section d'indicateurs)

### Tests à ajouter
- test_journal_enregistre_une_ligne_pour_chaque_action
- test_plateforme_journal_interdit_aux_non_super_admin

---

## Ajout 5 : Liste des agences (recherche + filtres + compteurs)

### Fichiers à modifier
- `app/Http/Controllers/AdministrationPlateformeController.php` - méthode index()
- `resources/views/plateforme/index.blade.php` - section agences

### Modifications
1. **Controller index()** :
   - Ajouter paramètres GET : $recherche, $type, $statut
   - Requête agences : where('nom', 'like', "%$recherche%") ou where('email', 'like', "%$recherche%")
   - Filtre type : if $type !== 'toutes', where('type', $type)
   - Filtre statut : if $statut !== 'toutes', where('active', $statut === 'actives')
   - Conserver filtres dans pagination : withQueryString()
   - Compteurs :
     - Solos : where('type', 'solo')->count()
     - Agences : where('type', 'agence')->count()
     - Demandes ce mois : whereMonth('created_at', now()->month)->count()

2. **Vue** :
   - Ajouter champ de recherche (nom ou e-mail)
   - Ajouter filtre "Type" : Toutes / Agences / Solos (select)
   - Ajouter filtre "Statut" : Toutes / Actives / Suspendues (select)
   - Afficher date de création de l'agence
   - Afficher type de l'agence
   - Mettre à jour les compteurs du haut de page (séparer Solos/Agences, ajouter Demandes ce mois)

### Tests à ajouter
- test_filtre_par_type_retourne_les_bons_resultats
- test_filtre_par_statut_retourne_les_bons_resultats
- test_recherche_par_nom_ou_email_retourne_les_bons_resultats

---

## Ordre des commits

1. Ajout 1 : Historique des demandes
2. Ajout 2 : Notes et contact sur les demandes
3. Ajout 3 : Suspension avec confirmation et motif
4. Ajout 4 : Journal des actions de la plateforme
5. Ajout 5 : Liste des agences (recherche + filtres + compteurs)

---

## Contraintes à respecter

- NE PAS modifier approuver() (création agence + compte admin + invitation + mail)
- NE PAS utiliser journal_activite (déjà rattaché à agence)
- Tous les POST protégés par CSRF et validés
- Tout contenu échappé (pas de {!! !!})
- Respecter le style existant (tokens CSS, composants x-*, français)
- Accessibilité : modales avec titre, focus géré
- Pas de données réelles dans seeders/tests
- 5 commits avec messages en français
- Environnement Windows : éditer fichiers directement avec outil d'édition, PAS de echo/Set-Content
- Après modifications : php artisan view:clear, php artisan migrate, php artisan test, php -l sur chaque fichier PHP modifié
