<?php

namespace App\Mail;

use App\Models\RechargeRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RechargeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * BUG FIX (Step 10.9 audit — production log, "View [view.name] not
     * found"): this class was generated via `make:mail` and never actually
     * filled in — the constructor took no arguments at all, and content()
     * pointed at the literal placeholder string 'view.name' instead of the
     * real Blade view. Every RechargeMail send was failing silently in the
     * queue (job status: FAIL) because of this, even though the DB
     * notification + wallet credit/reject logic worked fine.
     *
     * $recharge / $status / $reason are public so Mailable automatically
     * exposes them to the view under the same variable names — matches what
     * resources/views/mail/recharge.blade.php already expects
     * ($recharge, $status, $reason).
     */
    public function __construct(
        public RechargeRequest $recharge,
        public string $status,      // 'requested' | 'approved' | 'rejected'
        public ?string $reason = null,
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->status) {
            'requested' => 'নতুন Recharge Request জমা পড়েছে',
            'approved'  => 'আপনার Recharge Request অনুমোদিত হয়েছে',
            'rejected'  => 'আপনার Recharge Request বাতিল করা হয়েছে',
            default     => 'Recharge Request Update',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     *
     * BUG FIX: was 'view.name' (unfilled make:mail placeholder) — the real
     * view lives at resources/views/mail/recharge.blade.php, which Blade
     * resolves via the dot path 'mail.recharge'.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.recharge',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}