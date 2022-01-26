@component('mail::message')
# Recover account

Please click the button below to recover your account.

@component('mail::button', ['url' => $url])
Recover
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent