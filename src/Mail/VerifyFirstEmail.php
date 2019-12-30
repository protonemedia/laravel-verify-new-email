<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class VerifyFirstEmail extends Mailable implements ShouldQueue
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
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('verify-new-email::verifyFirstEmail', [
            'url' => $this->pendingUserEmail->verificationUrl(),
        ]);
    }
}
