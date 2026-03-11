@component('mail::message')
# {{ __('Verify New Email Address') }}

{{ __('Please click the button below to verify your new email address.') }}

@component('mail::button', ['url' => $url])
{{ __('Verify New Email Address') }}
@endcomponent

{{ __('If you did not update your email address, no further action is required.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent