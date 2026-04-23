<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyTipsNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Collection $tips) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚽ Today\'s Free Football Tips — ' . now()->format('d M Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.daily-tips',
        );
    }
}
