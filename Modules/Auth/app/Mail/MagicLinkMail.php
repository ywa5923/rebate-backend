<?php

namespace Modules\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
                'broker' => $this->magicLink->broker,
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
        
        return $frontendUrl . '/auth/magic-link?token=' . $this->magicLink->token;
    }

    /**
     * Get the email subject based on action.
     */
    private function getSubjectByAction(): string
    {
        return match ($this->magicLink->action) {
            'login' => 'Your Magic Link for Login',
            'registration' => 'Complete Your Broker Registration',
            'password_reset' => 'Reset Your Password',
            default => 'Your Magic Link',
        };
    }
}
