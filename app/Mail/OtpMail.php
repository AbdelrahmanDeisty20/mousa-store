<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $projectName;

    /**
     * Create a new queued OTP message instance.
     */
    public function __construct(
        public string $otpCode,
        public string $purpose = 'register',
        public int $expiresInMinutes = 10,
        public string $recipientName = '',
    ) {
        $this->projectName = config('app.name') ?: 'Mousa Store';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $isReset = str_contains($this->purpose, 'forgot_password');
        $subject = $isReset
            ? "{$this->projectName} | كود استعادة كلمة المرور - Password Reset Code"
            : "{$this->projectName} | كود تفعيل الحساب - Verification Code";

        $fromAddress = config('mail.from.address');
        $from = $fromAddress ? new Address($fromAddress, $this->projectName) : null;

        return new Envelope(
            from: $from,
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
