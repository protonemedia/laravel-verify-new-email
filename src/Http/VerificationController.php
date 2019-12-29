<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

class VerificationController
{
    use VerifiesPendingEmails;

    public function __invoke(Request $request, $token)
    {
    }
}
