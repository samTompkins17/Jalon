<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReinitialisationMotDePasseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $utilisateur, public string $token) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Réinitialisation de votre mot de passe Jalon');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reinitialisation-mot-de-passe',
            with: [
                'utilisateur' => $this->utilisateur,
                'lien' => route('mot-de-passe.formulaire', [
                    'token' => $this->token,
                    'email' => $this->utilisateur->email,
                ]),
            ],
        );
    }
}
