<?php
// app/Mail/ReferralBonusMail.php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralBonusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $referrer,
        public float $amount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Referral Bonus পেয়েছেন — AmeelHub');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.referral-bonus');
    }
}