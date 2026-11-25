<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use App\Enums\StatutActivation;
use App\Support\Format;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'agence_id', 'nom', 'email', 'telephone', 'password', 'role',
        'statut_activation', 'poste', 'photo_path', 'client_id',
        'est_transitaire_solo', 'derniere_connexion_at', 'password_changed_at',
        'preferences', 'permissions',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => RoleUtilisateur::class,
            'statut_activation' => StatutActivation::class,
            'est_transitaire_solo' => 'boolean',
            'est_super_admin' => 'boolean',
            'derniere_connexion_at' => 'datetime',
            'password_changed_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'preferences' => 'array',
            'permissions' => 'array',
        ];
    }

    /** Adresse publique de la photo de profil, ou null si aucune. */
    public function urlPhoto(): ?string
    {
        return $this->photo_path ? asset('storage/'.$this->photo_path) : null;
    }

    // ---------------------------------------------------------------- Relations

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    /** Compte portail rattaché à un client. */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function clientsCrees(): HasMany
    {
        return $this->hasMany(Client::class, 'transitaire_id');
    }

    public function dossiers(): HasMany
    {
        return $this->hasMany(Dossier::class, 'transitaire_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    // ------------------------------------------------------------------ Rôles

    public function estAdministrateur(): bool
    {
        return $this->role === RoleUtilisateur::Administrateur;
    }

    public function estSuperAdmin(): bool
    {
        return $this->est_super_admin === true;
    }

    public function estTransitaire(): bool
    {
        return $this->role === RoleUtilisateur::Transitaire;
    }

    public function estComptable(): bool
    {
        return $this->role === RoleUtilisateur::Comptable;
    }

    public function estClient(): bool
    {
        return $this->role === RoleUtilisateur::Client;
    }

    /** Seul l'administrateur gère l'équipe et les paramètres. */
    public function peutGererEquipe(): bool
    {
        return $this->estAdministrateur();
    }

    public function peutGererParametres(): bool
    {
        // Un transitaire solo administre sa propre structure : il règle
        // lui-même l'identité, la facturation et les alertes de son agence.
        return $this->estAdministrateur() || $this->est_transitaire_solo;
    }

    /** Administration : peut faire reculer un jalon. */
    public function peutReculerJalon(): bool
    {
        return $this->estAdministrateur();
    }

    public function peutFacturer(): bool
    {
        // Le transitaire solo facture lui-même ses dossiers.
        return $this->estAdministrateur() || $this->estComptable() || $this->est_transitaire_solo;
    }

    public function peutCreerDossiers(): bool
    {
        return $this->estAdministrateur() || $this->estTransitaire();
    }

    public function estActif(): bool
    {
        return $this->statut_activation === StatutActivation::Actif;
    }

    /** Permissions disponibles dans l'application. */
    public const PERMISSIONS = [
        'creer_dossiers' => 'Créer des dossiers',
        'modifier_dossiers' => 'Modifier des dossiers',
        'ajouter_documents' => 'Ajouter des documents',
        'gerer_clients' => 'Gérer les clients',
        'saisir_couts' => 'Saisir des coûts',
        'facturer' => 'Émettre des factures',
        'gerer_equipe' => "Gérer l'équipe",
    ];

    public const DESCRIPTIONS_PERMISSIONS = [
        'creer_dossiers' => "Permet d'ouvrir de nouveaux dossiers de transit et d'assigner un client",
        'modifier_dossiers' => "Mise à jour des régimes, navires, B/L et dates d'arrivée prévue",
        'ajouter_documents' => 'Téléversement et validation des connaissements, déclarations et fiches ECTN',
        'gerer_clients' => 'Création, modification et consultation des fiches annuaire client',
        'saisir_couts' => 'Enregistrement des débours portuaires, manutention et frais réels',
        'facturer' => 'Génération de factures pro forma ou définitives avec TVA',
        'gerer_equipe' => 'Invitation de nouveaux collaborateurs et modification des permissions',
    ];

    /** Droits attribués par défaut selon le rôle. */
    public static function permissionsParRole(RoleUtilisateur $role): array
    {
        return match ($role) {
            RoleUtilisateur::Administrateur => array_fill_keys(array_keys(self::PERMISSIONS), true),
            RoleUtilisateur::Transitaire => [
                'creer_dossiers' => true,
                'modifier_dossiers' => true,
                'ajouter_documents' => true,
                'gerer_clients' => true,
                'saisir_couts' => true,
                'facturer' => false,
                'gerer_equipe' => false,
            ],
            RoleUtilisateur::Comptable => [
                'creer_dossiers' => false,
                'modifier_dossiers' => false,
                'ajouter_documents' => false,
                'gerer_clients' => false,
                'saisir_couts' => true,
                'facturer' => true,
                'gerer_equipe' => false,
            ],
            RoleUtilisateur::Client => array_fill_keys(array_keys(self::PERMISSIONS), false),
        };
    }

    /** Permission effective (explicite ou héritée du rôle). */
    public function permission(string $cle): bool
    {
        $permissions = $this->permissions ?? self::permissionsParRole($this->role);

        return (bool) ($permissions[$cle] ?? false);
    }

    public function getInitialesAttribute(): string
    {
        return Format::initiales($this->nom);
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }
}
