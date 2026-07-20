<?php
// app/Mail/CvStatusMail.php
namespace App\Mail;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CvStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Worker $worker,
        public string $status, // submitted|approved|rejected
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'submitted' => 'নতুন CV জমা পড়েছে — AmeelHub',
            'approved'  => 'আপনার CV অনুমোদিত হয়েছে — AmeelHub',
            'rejected'  => 'আপনার CV বাতিল হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.cv-status');
    }
}