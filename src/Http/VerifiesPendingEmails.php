<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\RedirectsUsers;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

trait VerifiesPendingEmails
{
    use RedirectsUsers;

    /**
     * Mark the user's new email address as verified.
     *
     * @param  string $token
     *
     * @throws \ProtoneMedia\LaravelVerifyNewEmail\Http\InvalidVerificationLinkException
     */
    public function verify(string $token)
    {
        PendingUserEmail::whereToken($token)->firstOr(['*'], function () {
            throw new InvalidVerificationLinkException(
                __('The verification link is not valid anymore.')
            );
        })->activate();

        return redirect($this->redirectPath())->with('verified', true);
    }
}
