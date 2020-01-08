<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyFirstEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $pendingUserEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Model $pendingUserEmail)
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
        $this->subject(__('Verify Email Address'));

        return $this->markdown('verify-new-email::verifyFirstEmail', [
            'url' => $this->pendingUserEmail->verificationUrl(),
        ]);
    }
}
