<?php
// app/Mail/DealCompletedMail.php
namespace App\Mail;

use App\Models\JobDeal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DealCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobDeal $deal,
        public ?string $pdfPath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Deal সম্পন্ন হয়েছে ✅ — AmeelHub');
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.deal-completed');
    }

    public function attachments(): array
    {
        if ($this->pdfPath) {
            return [Attachment::fromStorageDisk('private_docs', $this->pdfPath)];
        }
        return [];
    }
}