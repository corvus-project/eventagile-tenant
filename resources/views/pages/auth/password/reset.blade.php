<?php

use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;

new #[Layout('layouts.auth')] class extends Component
{
    #[Validate('required|email')]
    public ?string $email = null;
    public ?string $emailSentMessage = null;
    public ?string $captchaToken = null;

    public function sendResetPasswordLink($token = null)
    {
        if ($token) {
            $this->captchaToken = $token;
        }

        $query = http_build_query([
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $this->captchaToken,
        ]);
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?' . $query);
        $captchaLevel = $response->json('score');

        throw_if($captchaLevel <= 0.5, ValidationException::withMessages([
            'captchaToken' => __('Error on captcha verification. Please, refresh the page and try again.')
        ]));
        $this->validate();

        $response = Password::broker()->sendResetLink(['email' => $this->email]);

        if ($response == Password::RESET_LINK_SENT) {
            $this->emailSentMessage = 'We have sent the your password reset link to your email address.';
            return;
        }

        $this->addError('email', trans($response));
    }
};

?>

<x-slot name="title">
    Login to EventAgile
</x-slot>

<div class="flex flex-col items-stretch justify-center w-screen min-h-screen py-10 sm:items-center">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-ui.link href="{{ route('tenant.home') }}">
            <x-ui.logo class="w-auto h-10 mx-auto text-gray-700 fill-current dark:text-gray-100" />
        </x-ui.link>

        <h2 class="mt-5 text-2xl font-extrabold leading-9 text-center text-gray-800 dark:text-gray-200">
            Reset password
        </h2>
        <div class="text-sm leading-5 text-center text-gray-600 dark:text-gray-400 space-x-0.5">
            <span>Or</span>
            <x-ui.text-link href="{{ route('login') }}">return to login</x-ui.text-link>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-10 py-0 sm:py-8 sm:shadow-sm sm:bg-white dark:sm:bg-gray-950/50 dark:border-gray-200/10 sm:border sm:rounded-lg border-gray-200/60">
            @if ($emailSentMessage)
            <div class="p-4 rounded-md bg-green-50 dark:bg-green-600">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-green-400 dark:text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="ml-3">
                        <p class="text-sm font-medium leading-5 text-green-800 dark:text-green-200">
                            {{ $emailSentMessage }}
                        </p>
                    </div>
                </div>
            </div>
            @else
            @error('captchaToken')
            <div class="bg-red-300 text-red-700 p-3 rounded">{{ $message }}</div>
            @enderror
            <form onsubmit="handleSubmit(event)" class="space-y-6">
                <x-ui.input label="Email address" type="email" id="email" name="email" wire:model="email" />



                <x-button label="Send password reset link" rounded="md" class="btn-primary" type="primary" submit="true" />


            </form>
            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.public_key') }}"></script>
            <script>
                function handleSubmit(event) {
                    event.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ config("services.recaptcha.public_key") }}', {
                                action: 'submit'
                            })
                            .then(function(token) {
                                @this.call('sendResetPasswordLink', token);
                            });
                    })
                }
            </script>
            @endif
        </div>
    </div>


</div>