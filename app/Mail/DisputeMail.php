<?php
// app/Mail/DisputeMail.php
namespace App\Mail;

use App\Models\JobDealMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisputeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobDealMilestone $milestone,
        public string $status, // raised|resolved
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'raised'   => '⚠ জরুরি: Dispute উত্থাপিত হয়েছে — AmeelHub',
            'resolved' => 'Dispute সমাধান হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.dispute');
    }
}