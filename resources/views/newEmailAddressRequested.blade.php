@component('mail::message')
# {{ __('Email Address Change Requested') }}

{{ __('A request has been made to change the email address associated with your account. If you did not make this request, please secure your account immediately.') }}

{{ __('Thanks') }},<br>
{{ config('app.name') }}
@endcomponent