<?php
// app/Mail/WithdrawalMail.php
namespace App\Mail;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WithdrawalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WithdrawalRequest $withdrawal,
        public string $status, // requested|approved|rejected
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'requested' => 'নতুন Withdrawal Request — AmeelHub',
            'approved'  => 'আপনার Withdrawal অনুমোদিত হয়েছে — AmeelHub',
            'rejected'  => 'আপনার Withdrawal বাতিল হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.withdrawal');
    }
}