---
name: verify-new-email-development
description: Build and work with protonemedia/laravel-verify-new-email features including verifying new email addresses, managing pending emails, customizing verification mailables, and handling the email update workflow.
license: MIT
metadata:
  author: Protone Media
---

# Verify New Email Development

## Overview
Use protonemedia/laravel-verify-new-email to verify new email addresses before replacing the old one. Supports first-time email verification, auto-login after verification, customizable mailables, and signed temporary URLs.

## When to Activate
- Activate when working with email address changes, email update verification, or pending email workflows in Laravel.
- Activate when code references `MustVerifyNewEmail`, `PendingUserEmail`, `VerifyNewEmail`, `VerifyFirstEmail`, or the `pending_user_emails` table.
- Activate when the user wants to initiate, resend, clear, or verify a new email address on an Eloquent model.

## Scope
- In scope: initiating email changes, sending verification mails, verifying pending emails, customizing mailables and routes, handling auto-login after verification.
- Out of scope: general Laravel email verification without email changes, non-Laravel frameworks.

## Workflow
1. Identify the task (model setup, initiating email change, customizing mailables, handling verification, etc.).
2. Read `references/verify-new-email-guide.md` and focus on the relevant section.
3. Apply the patterns from the reference, keeping code minimal and Laravel-native.

## Core Concepts

### Model Setup
The User model must implement `MustVerifyEmail` and use the `MustVerifyNewEmail` trait:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use MustVerifyNewEmail, Notifiable;
}
```

### Initiating an Email Change
```php
$user->newEmail('me@newcompany.com');
```

### Checking and Managing Pending Emails
```php
$user->getPendingEmail();
$user->resendPendingEmailVerificationMail();
$user->clearPendingEmail();
```

### Customizing the Mailable
```php
$user->newEmail('me@newcompany.com', function ($mailable, $pendingUserEmail) {
    $mailable->subject('Please verify your new email');
});
```

### Overriding First Email Verification
```php
public function sendEmailVerificationNotification()
{
    $this->newEmail($this->getEmailForVerification());
}
```

## Do and Don't

Do:
- Always implement the `MustVerifyEmail` interface alongside the `MustVerifyNewEmail` trait.
- Run `php artisan vendor:publish --provider="ProtoneMedia\LaravelVerifyNewEmail\ServiceProvider"` to publish migrations, config, and views.
- Use `$user->newEmail()` to initiate email changes instead of directly updating the `email` column.
- Use the `verify-new-email.php` config file to customize redirect paths, auto-login, and mailables.
- Use `$user->getPendingEmail()` to display the pending email address to users.

Don't:
- Don't update the user's `email` column directly — use `$user->newEmail()` so the change goes through verification.
- Don't forget to run the published migration to create the `pending_user_emails` table.
- Don't assume the user is logged in after verification — check the `login_after_verification` config setting.
- Don't call `resendPendingEmailVerificationMail()` without a prior `newEmail()` call — it throws `ModelNotFoundException` if no pending email exists.

## References
- `references/verify-new-email-guide.md`
