<?php
// app/Mail/EscrowMail.php
namespace App\Mail;

use App\Models\JobDeal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EscrowMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobDeal $deal) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Escrow Hold নিশ্চিত হয়েছে — AmeelHub');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.escrow');
    }
}