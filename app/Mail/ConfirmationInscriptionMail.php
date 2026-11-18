<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmationInscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $utilisateur, public string $lien) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Confirmez votre adresse e-mail Jalon');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.confirmation-inscription',
            with: [
                'utilisateur' => $this->utilisateur,
                'lien' => $this->lien,
            ],
        );
    }
}
