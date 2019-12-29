<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyNewEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail
     */
    public $pendingUserEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(PendingUserEmail $pendingUserEmail)
    {
        $this->pendingUserEmail = $pendingUserEmail;
    }

    /**
     * Creates a temporary signed URL to verify the pending email.
     *
     * @return string
     */
    public function verificationUrl():string
    {
        return URL::temporarySignedRoute(
            config('verify-new-email.route')?: 'pendingEmail.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'token' => $this->pendingUserEmail->token,
            ]
        );
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('verify-new-email::emails.verifyNewEmail', [
            'url' => $this->verificationUrl(),
        ]);
    }
}
