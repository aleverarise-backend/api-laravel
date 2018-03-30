@component('mail::message')
# Hola {{ $user->name }}

gracias por crear una cuenta. por favor verificala usando el siguiente boton:

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar mi Cuenta
@endcomponent

Thanks,<br>
{{ config('Api RestFull') }}
@endcomponent
