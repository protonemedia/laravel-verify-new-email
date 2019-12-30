<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

use Illuminate\Support\Facades\Auth;
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

trait VerifiesPendingEmails
{
    /**
     * Mark the user's new email address as verified.
     *
     * @param  string $token
     *
     * @throws \ProtoneMedia\LaravelVerifyNewEmail\Http\InvalidVerificationLinkException
     */
    public function verify(string $token)
    {
        $user = PendingUserEmail::whereToken($token)->firstOr(['*'], function () {
            throw new InvalidVerificationLinkException(
                __('The verification link is not valid anymore.')
            );
        })->activate();

        if (config('verify-new-email.login_after_verification')) {
            Auth::guard()->login($user);
        }

        return redirect(config('verify-new-email.redirect_to'))->with('verified', true);
    }
}
