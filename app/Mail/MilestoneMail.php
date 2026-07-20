<?php
// app/Mail/MilestoneMail.php
namespace App\Mail;

use App\Models\JobDealMilestone;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class MilestoneMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobDealMilestone $milestone,
        public string $status, // worker_confirmed|agent_confirmed|released
        public ?string $pdfPath = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'worker_confirmed' => 'Worker Milestone Confirm করেছেন — AmeelHub',
            'agent_confirmed'  => 'Agent Milestone Confirm করেছেন — AmeelHub',
            'released'         => 'Milestone Release হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.milestone');
    }

    public function attachments(): array
    {
        if ($this->status === 'released' && $this->pdfPath) {
            return [Attachment::fromStorageDisk('private_docs', $this->pdfPath)];
        }
        return [];
    }
}