<?php

return [
    /**
     * Here you can specify the name of a custom route to handle the verification.
     */
    'route' => null,

    /**
     * Here you can specify the path to redirect to after verification.
     */
    'redirect_to' => '/home',

    /**
     * Wether to login the user after successfully verifying its email.
     */
    'login_after_verification' => true,

    /**
     * Should the user be permanently "remembered" by the application.
     */
    'login_remember' => false,

    /**
     * Configure when a recovery email is being send. Set to false to disable this feature.
     * 
     * Options: false, 'before_verification', 'after_verification'
     */
    'send_recovery_email' => false,

    /**
     * Model class that will be used to store and retrieve the tokens.
     */
    'model' => \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail::class,

    /**
     * The Mailable that will be sent when the User wants to verify
     * its initial email address (that got used with registering).
     */
    'mailable_for_first_verification' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyFirstEmail::class,

    /**
     * The Mailable that will be sent when the User wants to verify
     * a new email address, for example when the User wants to
     * update its email address.
     */
    'mailable_for_new_email' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail::class,

    /**
     * The Mailable that will be sent when the User's email address has been updated.
     */
    'mailable_for_new_email_notification' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\NotifyOldEmail::class,

    /**
     * The Mailable that will be sent for recovery.
     */
    'mailable_for_recovery_email' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\RecoverEmail::class
];
