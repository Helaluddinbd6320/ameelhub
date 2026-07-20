<?php
// app/Mail/AgentVerificationMail.php
namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AgentVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $agent,
        public string $status, // verified|rejected
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'verified' => 'আপনার Agent Account Verified — AmeelHub',
            'rejected' => 'আপনার Agent Verification বাতিল হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.agent-verification');
    }
}