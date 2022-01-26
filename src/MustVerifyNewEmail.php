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
    public function newEmail(string $email): ?Model
    {
        $currentEmail = $this->getEmailForVerification();

        if ($currentEmail === $email && $this->hasVerifiedEmail()) {
            return null;
        }

        if (config('verify-new-email.send_recovery_email') === 'before_verification') {
            $this->newRecovery($currentEmail);
        }

        return $this->createPendingUserEmailModel($email)->tap(function ($model) {
            $this->sendPendingEmailVerificationMail($model);
        });
    }

    public function getEmailVerificationModel(): Model
    {
        $modelClass = config('verify-new-email.model');

        if (!$modelClass) {
            throw new InvalidEmailVerificationModelException;
        }

        return app($modelClass);
    }

    /**
     * Creates new PendingUserModel model for the given email.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createPendingUserEmailModel(string $email): Model
    {
        $this->clearPendingEmail();

        return $this->getEmailVerificationModel()->create([
            'user_type' => get_class($this),
            'user_id'   => $this->getKey(),
            'email'     => $email,
            'token'     => Password::broker()->getRepository()->createNewToken(),
            'type'      => PendingUserEmail::TYPE_PENDING,
        ]);
    }

    /**
     * Returns the pending email address.
     *
     * @return string|null
     */
    public function getPendingEmail(): ?string
    {
        return $this->getEmailVerificationModel()->forUser($this)->value('email');
    }

    /**
     * Deletes the pending email address models for this user, with type pending.
     *
     * @return void
     */
    public function clearPendingEmail()
    {
        $this->getEmailVerificationModel()->forUser($this)->where('type', PendingUserEmail::TYPE_PENDING)->get()->each->delete();
    }

    /**
     * Deletes the pending email address models for this user, with type recover.
     *
     * @return void
     */
    public function clearRecoverEmail()
    {
        $this->getEmailVerificationModel()->forUser($this)->where('type', PendingUserEmail::TYPE_RECOVER)->get()->each->delete();
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
    public function resendPendingEmailVerificationMail(): ?Model
    {
        $pendingUserEmail = $this->getEmailVerificationModel()->forUser($this)->firstOrFail();

        return $this->newEmail($pendingUserEmail->email);
    }

    /**
     * Create a new PendingUserEmail model for recovery.
     *
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function newRecovery(string $email): ?Model
    {
        return $this->createRecoveryPendingUserEmailModel($email)->tap(function ($model) {
            $this->sendRecoveryMail($model);
        });
    }

    /**
     * Creates new PendingUserModel model for the given email, with type recover.
     *
     * @param string $currentEmail
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createRecoveryPendingUserEmailModel(string $currentEmail): Model
    {
        $this->clearRecoverEmail();

        return $this->getEmailVerificationModel()->create([
            'user_type' => get_class($this),
            'user_id'   => $this->getKey(),
            'email'     => $currentEmail,
            'token'     => Password::broker()->getRepository()->createNewToken(),
            'type'      => PendingUserEmail::TYPE_RECOVER,
        ]);
    }

    /**
     * Sends the recoverEmail Mailable to the old email address.
     *
     * @param \Illuminate\Database\Eloquent\Model $pendingUserEmail
     * @return mixed
     */
    public function sendRecoveryMail(Model $pendingUserEmail)
    {
        $mailableClass = config('verify-new-email.mailable_for_recovery_email');

        $mailable = new $mailableClass($pendingUserEmail);

        return Mail::to($pendingUserEmail->email)->send($mailable);
    }
}
