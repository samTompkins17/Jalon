<?php

namespace App\Models;

use App\Enums\RoleUtilisateur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'agence_id', 'user_id', 'client_id', 'email', 'nom', 'role',
        'token', 'expires_at', 'accepted_at', 'invited_by',
    ];

    protected function casts(): array
    {
        return [
            'role' => RoleUtilisateur::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public static function genererToken(): string
    {
        return Str::random(64);
    }

    public function estExpiree(): bool
    {
        return $this->accepted_at !== null || $this->expires_at->isPast();
    }

    public function getLienAttribute(): string
    {
        return route('activation.show', $this->token);
    }

    public function getStatutAttribute(): string
    {
        if ($this->accepted_at) {
            return 'acceptee';
        }

        return $this->expires_at->isPast() ? 'expiree' : 'en_attente';
    }
}
