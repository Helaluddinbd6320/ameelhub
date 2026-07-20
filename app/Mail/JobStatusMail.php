<?php
// app/Mail/JobStatusMail.php
namespace App\Mail;

use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobPost $job,
        public string $status, // approved|rejected|auto_closed
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'approved'    => 'আপনার Job Post অনুমোদিত হয়েছে — AmeelHub',
            'rejected'    => 'আপনার Job Post বাতিল হয়েছে — AmeelHub',
            'auto_closed' => 'আপনার Job Post বন্ধ হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.job-status');
    }
}