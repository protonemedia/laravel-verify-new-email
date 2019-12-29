<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;

trait MustVerifyNewEmail
{
    public function newEmail($email):PendingEmailAddress
    {
        PendingEmailAddress::forUser($this)->get()->each->delete();

        $token = Password::broker()->getRepository()->createNewToken();

        $pendingEmailAddress = PendingEmailAddress::create([
            'user_type' => get_class($this),
            'user_id'   => $this->getKey(),
            'email'     => $email,
            'token'     => Hash::make($token),
        ]);

        $this->sendPendingEmailVerificationMail($email, $token);

        return $pendingEmailAddress;
    }

    public function sendPendingEmailVerificationMail($newEmail, $token)
    {
        Mail::to($newEmail)->send(new VerifyNewEmail($token));
    }

    public function resendPendingEmailVerificationMail():PendingEmailAddress
    {
        $pendingEmailAddress = PendingEmailAddress::forUser($this)->firstOrFail();

        return $this->newEmail($pendingEmailAddress->email);
    }
}
