@component('mail::message')
# Hola {{ $user->name }}

Has cambiado tu correo electronico. Por favor verificalo en el siguiente boton.

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar mi Cuenta
@endcomponent

Thanks,<br>
{{ config('Api RestFull') }}
@endcomponent
