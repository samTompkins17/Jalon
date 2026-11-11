<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationEmployeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $invitation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre accès à Jalon — '.$this->invitation->agence?->nom,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invitation-employe',
            with: [
                'invitation' => $this->invitation,
                'lien' => $this->invitation->lien,
                'auteur' => $this->invitation->auteur?->nom,
            ],
        );
    }
}
