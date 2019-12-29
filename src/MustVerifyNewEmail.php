<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;

trait MustVerifyNewEmail
{
    /**
     * Deletes all previous attempts for this user, creates a new model/token
     * to verify the given email address and send the verification URL
     * to the new email address.
     *
     * @param string $email
     * @return \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail|null
     */
    public function newEmail(string $email):?PendingUserEmail
    {
        PendingUserEmail::forUser($this)->get()->each->delete();

        if ($this->email === $email) {
            return null;
        }

        return tap(PendingUserEmail::create([
            'user_type' => get_class($this),
            'user_id'   => $this->getKey(),
            'email'     => $email,
            'token'     => Password::broker()->getRepository()->createNewToken(),
        ]), function ($pendingUserEmail) {
            $this->sendPendingUserEmailVerificationMail($pendingUserEmail);
        });
    }

    /**
     * Sends the VerifyNewEmail Mailable to the new email address.
     *
     * @param \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail $pendingUserEmail
     * @return mixed
     */
    public function sendPendingUserEmailVerificationMail(PendingUserEmail $pendingUserEmail)
    {
        return Mail::to($pendingUserEmail->email)->send(new VerifyNewEmail($pendingUserEmail));
    }

    /**
     * Grabs the pending user email address, generates a new token and sends the Mailable.
     *
     * @return \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail|null
     */
    public function resendPendingUserEmailVerificationMail():?PendingUserEmail
    {
        $pendingUserEmail = PendingUserEmail::forUser($this)->firstOrFail();

        return $this->newEmail($pendingUserEmail->email);
    }
}
