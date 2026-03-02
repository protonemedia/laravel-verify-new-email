# Laravel Verify New Email Reference

Complete reference for `protonemedia/laravel-verify-new-email`. Full documentation: https://github.com/protonemedia/laravel-verify-new-email

## Installation

```bash
composer require protonemedia/laravel-verify-new-email
php artisan vendor:publish --provider="ProtoneMedia\LaravelVerifyNewEmail\ServiceProvider"
php artisan migrate
```

Publishing creates:
- Migration: `create_pending_user_emails` table
- Config: `config/verify-new-email.php`
- Views: `resources/views/vendor/verify-new-email/`

## Model Setup

Implement `MustVerifyEmail` and use `MustVerifyNewEmail`:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyNewEmail, Notifiable;
}
```

## Initiating an Email Change

Call `newEmail()` on the user. This creates a token in the `pending_user_emails` table and sends a verification mail to the new address. The current email stays unchanged until the new one is verified.

```php
$user->newEmail('me@newcompany.com');
```

If the new email is identical to the current verified email, `newEmail()` returns `null` and no mail is sent.

### With mailable customization

Pass a callback as the second argument to customize the mailable before sending:

```php
$user->newEmail('me@newcompany.com', function ($mailable, $pendingUserEmail) {
    $mailable->subject('Confirm your new email');
    $mailable->cc('admin@company.com');
});
```

## Checking Pending Emails

```php
// Get the pending email address (or null if none)
$pending = $user->getPendingEmail();

// Display to the user
if ($pending) {
    echo "Verification email sent to: {$pending}";
}
```

## Resending Verification Mail

Regenerates the token and resends the verification mail for the existing pending email:

```php
$user->resendPendingEmailVerificationMail();
```

This throws `ModelNotFoundException` if no pending email exists. Check with `getPendingEmail()` first.

## Clearing Pending Emails

Remove all pending email records for the user without applying any change:

```php
$user->clearPendingEmail();
```

## Overriding Default Laravel Email Verification

To use this package for initial email verification (after registration), override `sendEmailVerificationNotification()`:

```php
class User extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyNewEmail, Notifiable;

    public function sendEmailVerificationNotification()
    {
        $this->newEmail($this->getEmailForVerification());
    }
}
```

This replaces Laravel's built-in verification flow with one that supports unauthenticated verification and auto-login.

## Verification Flow

When the user clicks the verification link:

1. The signed URL is validated by Laravel's `signed` middleware.
2. The `PendingUserEmail` record is looked up by token.
3. `activate()` is called: the user's `email` column is updated, `markEmailAsVerified()` is called, and the `Verified` event is dispatched.
4. All `PendingUserEmail` records with the same email address are deleted.
5. If `login_after_verification` is enabled, the user is logged in.
6. The user is redirected to the `redirect_to` path with `verified=true` in the session.

If the token is invalid or expired, `InvalidVerificationLinkException` is thrown.

## Configuration

The `config/verify-new-email.php` file:

```php
return [
    // Custom route name for verification URL (default: 'pendingEmail.verify')
    'route' => null,

    // Path to redirect to after verification
    'redirect_to' => '/home',

    // Auto-login user after verification
    'login_after_verification' => true,

    // Use "remember me" cookie when logging in
    'login_remember' => false,

    // Model class for pending email records
    'model' => \ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail::class,

    // Mailable for first-time email verification (after registration)
    'mailable_for_first_verification' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyFirstEmail::class,

    // Mailable for verifying updated email addresses
    'mailable_for_new_email' => \ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail::class,
];
```

Token expiration is controlled by `config('auth.verification.expire')`, which defaults to 60 minutes.

## Custom Mailables

Create a custom mailable class and register it in the config:

```php
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomVerifyNewEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pendingUserEmail;

    public function __construct(Model $pendingUserEmail)
    {
        $this->pendingUserEmail = $pendingUserEmail;
    }

    public function build()
    {
        return $this->markdown('emails.verify-new-email', [
            'url' => $this->pendingUserEmail->verificationUrl(),
        ]);
    }
}
```

Then update the config:

```php
'mailable_for_new_email' => \App\Mail\CustomVerifyNewEmail::class,
```

## Custom Verification Controller

Override the send method on the User model for full control:

```php
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyNewEmail, Notifiable;

    public function sendPendingEmailVerificationMail(PendingUserEmail $pendingUserEmail)
    {
        // Custom logic: use a notification, different mail driver, etc.
    }
}
```

## Custom Verification Route

Set a custom route name in the config:

```php
'route' => 'user.email.verify',
```

Then define the route in your application:

```php
use ProtoneMedia\LaravelVerifyNewEmail\Http\VerifyNewEmailController;

Route::get('verify-email/{token}', [VerifyNewEmailController::class, 'verify'])
    ->middleware(['web', 'signed'])
    ->name('user.email.verify');
```

The `{token}` parameter is required. The route must use the `signed` middleware.

## Customizing Email Views

Published views are in `resources/views/vendor/verify-new-email/`:

- `verifyNewEmail.blade.php` — sent when a user changes their email address
- `verifyFirstEmail.blade.php` — sent when a user verifies their email for the first time

Both views receive a `$url` variable containing the signed verification URL:

```blade
@component('mail::message')
# Verify Your Email

Click the button below to verify your new email address.

@component('mail::button', ['url' => $url])
Verify Email Address
@endcomponent

@endcomponent
```

## Database Schema

The `pending_user_emails` table:

```php
Schema::create('pending_user_emails', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->morphs('user');           // user_type, user_id
    $table->string('email')->index();
    $table->string('token');
    $table->timestamp('created_at')->nullable();
});
```

## PendingUserEmail Model

Key methods on the `PendingUserEmail` model:

```php
use ProtoneMedia\LaravelVerifyNewEmail\PendingUserEmail;

// Find by token
$pending = PendingUserEmail::whereToken($token)->first();

// Get the associated user
$user = $pending->user;

// Activate: updates user email and cleans up records
$pending->activate();

// Get the signed verification URL
$url = $pending->verificationUrl();

// Scope: find records for a specific user
$records = PendingUserEmail::forUser($user)->get();
```

## Events

The `Illuminate\Auth\Events\Verified` event is dispatched when:
- A new email address is activated (email was changed)
- A first email verification is completed

```php
use Illuminate\Auth\Events\Verified;

Event::listen(Verified::class, function (Verified $event) {
    $user = $event->user;
    // Handle post-verification logic
});
```

## Testing

### Asserting verification mail was sent

```php
use Illuminate\Support\Facades\Mail;
use ProtoneMedia\LaravelVerifyNewEmail\Mail\VerifyNewEmail;

Mail::fake();

$user->newEmail('new@example.com');

Mail::assertSent(VerifyNewEmail::class, function ($mail) {
    return $mail->hasTo('new@example.com');
});
```

### Asserting pending email was created

```php
$user->newEmail('new@example.com');

$this->assertDatabaseHas('pending_user_emails', [
    'user_id' => $user->id,
    'user_type' => get_class($user),
    'email' => 'new@example.com',
]);
```

### Asserting email was updated after verification

```php
$pendingUserEmail = $user->newEmail('new@example.com');

// Simulate clicking the verification link
$pendingUserEmail->activate();

$this->assertEquals('new@example.com', $user->fresh()->email);
$this->assertTrue($user->fresh()->hasVerifiedEmail());
```

### Testing the full verification flow

```php
$pendingUserEmail = $user->newEmail('new@example.com');

$this->get($pendingUserEmail->verificationUrl())
    ->assertRedirect(config('verify-new-email.redirect_to'));

$this->assertEquals('new@example.com', $user->fresh()->email);
```
