<?php

namespace Modules\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\MagicLink;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public MagicLink $magicLink;
    public string $magicLinkUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(MagicLink $magicLink)
    {
        $this->magicLink = $magicLink;
        $this->magicLinkUrl = $this->buildMagicLinkUrl();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->getSubjectByAction();
        
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'auth::emails.magic-link',
            with: [
                'magicLink' => $this->magicLink,
                'magicLinkUrl' => $this->magicLinkUrl,
                'subject' => $this->magicLink->subject,
                'action' => $this->magicLink->action,
                'expiresAt' => $this->magicLink->expires_at,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the magic link URL.
     */
    private function buildMagicLinkUrl(): string
    {
        $baseUrl = config('app.url');
        $frontendUrl = config('app.frontend_url', $baseUrl);
        
        return $frontendUrl . '/en/verify-token?token=' . $this->magicLink->token;
    }

    /**
     * Get the email subject based on action.
     */
    private function getSubjectByAction(): string
    {
        return match ($this->magicLink->action) {
            'login' => 'FXREBATE - Your Magic Link for Login',
            'registration' => 'FXREBATE - Complete Your Broker Registration',
            'password_reset' => 'FXREBATE - Reset Your Password',
            default => 'FXREBATE - Your Magic Link for Login',
        };
    }
}
