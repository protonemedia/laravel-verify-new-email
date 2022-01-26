@component('mail::message')
# Your email address has been changed

If you did not change your email address, click the button below to recover your account.

@component('mail::button', ['url' => $url])
Recover
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent