<?php
// app/Mail/NokMail.php
namespace App\Mail;

use App\Models\AgentNok;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NokMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AgentNok $nok,
        public string $status, // sent|accepted
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'sent'     => 'নতুন Job Offer এসেছে — AmeelHub',
            'accepted' => 'আপনার Nok গ্রহণ করা হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.nok');
    }
}