<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

trait MustVerifyNewEmail
{
    /**
     * Deletes all previous attempts for this user, creates a new model/token
     * to verify the given email address and send the verification URL
     * to the new email address.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function newEmail(string $email):?Model
    {
        if ($this->getEmailForVerification() === $email && $this->hasVerifiedEmail()) {
            return null;
        }

        return $this->createPendingUserEmailModel($email)->tap(function ($model) {
            $this->sendPendingEmailVerificationMail($model);
        });
    }

    /**
     * Createsa new PendingUserModel model for the given email.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createPendingUserEmailModel(string $email): Model
    {
        $this->clearPendingEmail();

        return app(config('verify-new-email.model'))->create([
            'user_type' => get_class($this),
            'user_id'   => $this->getKey(),
            'email'     => $email,
            'token'     => Password::broker()->getRepository()->createNewToken(),
        ]);
    }

    /**
     * Returns the pending email address.
     *
     * @return string|null
     */
    public function getPendingEmail():?string
    {
        return app(config('verify-new-email.model'))->forUser($this)->value('email');
    }

    /**
     * Deletes the pending email address models for this user.
     *
     * @return void
     */
    public function clearPendingEmail()
    {
        app(config('verify-new-email.model'))->forUser($this)->get()->each->delete();
    }

    /**
     * Sends the VerifyNewEmail Mailable to the new email address.
     *
     * @param \Illuminate\Database\Eloquent\Model $pendingUserEmail
     * @return mixed
     */
    public function sendPendingEmailVerificationMail(Model $pendingUserEmail)
    {
        $mailableClass = config('verify-new-email.mailable_for_first_verification');

        if ($pendingUserEmail->User->hasVerifiedEmail()) {
            $mailableClass = config('verify-new-email.mailable_for_new_email');
        }

        $mailable = new $mailableClass($pendingUserEmail);

        return Mail::to($pendingUserEmail->email)->send($mailable);
    }

    /**
     * Grabs the pending user email address, generates a new token and sends the Mailable.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resendPendingEmailVerificationMail():?Model
    {
        $pendingUserEmail = app(config('verify-new-email.model'))->forUser($this)->firstOrFail();

        return $this->newEmail($pendingUserEmail->email);
    }
}
