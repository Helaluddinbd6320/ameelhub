<?php
// app/Mail/SelectionMail.php
namespace App\Mail;

use App\Models\Worker;
use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SelectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Worker $worker,
        public JobPost $job,
        public string $status, // selected|accepted
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'selected' => 'আপনি Selected হয়েছেন — AmeelHub',
            'accepted' => 'Worker আপনার Selection গ্রহণ করেছেন — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.selection');
    }
}