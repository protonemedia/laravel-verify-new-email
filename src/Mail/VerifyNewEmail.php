<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class VerifyNewEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function verificationUrl()
    {
        return URL::temporarySignedRoute(
            'pendingEmail.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'token' => $this->token,
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
