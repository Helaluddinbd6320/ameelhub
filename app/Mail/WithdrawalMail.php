<?php
// app/Mail/RechargeMail.php
namespace App\Mail;

use App\Models\RechargeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RechargeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RechargeRequest $recharge,
        public string $status, // requested|approved|rejected
        public ?string $reason = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'requested' => 'নতুন Recharge Request — AmeelHub',
            'approved'  => 'আপনার Recharge অনুমোদিত হয়েছে — AmeelHub',
            'rejected'  => 'আপনার Recharge Request বাতিল হয়েছে — AmeelHub',
        ];
        return new Envelope(subject: $subjects[$this->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.recharge');
    }
}