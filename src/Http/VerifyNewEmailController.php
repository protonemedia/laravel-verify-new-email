<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Http;

use Illuminate\Routing\Controller;

class VerifyNewEmailController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify New Email Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for verifying and activating new user email
    | addresses and uses a simple trait to include this behavior.
    |
    */

    use VerifiesPendingEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('throttle:6,1');
    }
}
