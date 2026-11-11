<?php

namespace App\Mail;

use App\Models\Alerte;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteSurestarieMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Alerte $alerte, public User $destinataire) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.config('app.name').'] '.$this->alerte->titre.' — '.$this->alerte->cible,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.alerte-surestarie');
    }
}
