{{-- Laravel Verify New Email Guidelines for AI Code Assistants --}}
{{-- Source: https://github.com/protonemedia/laravel-verify-new-email --}}
{{-- License: MIT | (c) Protone Media --}}

## Verify New Email

- `protonemedia/laravel-verify-new-email` adds support for verifying new email addresses when a user updates their email, preventing the old address from being replaced until the new one is verified.
- Always activate the `verify-new-email-development` skill when working with email change verification, pending emails, or any code that uses the `MustVerifyNewEmail` trait or the `PendingUserEmail` model.
