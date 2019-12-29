<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

use Illuminate\Auth\AuthenticationException;

class InvalidVerificationLinkException extends AuthenticationException
{
}
