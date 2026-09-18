<?php

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;


new #[Layout('layouts.auth')] class extends Component
{
    #[Validate('required|string|email')]
    public $email = '';

    #[Validate('required|string', message: 'Please provide a password')]
    public $password = '';

    public $remember = false;

    public ?string $captchaToken = null;

    #[On('formSubmitted')]
    public function authenticate(?string $token = null)
    {
        $this->captchaToken = $token;
        Log::info('Starting authentication process for email: ' . $this->email);

        $this->validate();

        $query = http_build_query([
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $this->captchaToken,
        ]);

        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?' . $query);
        $captchaLevel = $response->json('score');

        throw_if($captchaLevel <= 0.5, ValidationException::withMessages([
            'captchaToken' => __('Error on captcha verification. Please, refresh the page and try again.')
        ]));

        Log::info('Login attempt', ['email' => $this->email]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            Log::warning('Failed login attempt', ['email' => $this->email, 'ip' => request()->ip(), 'captcha_score' => $captchaLevel]);
            $this->addError('email', trans('auth.failed'));
            return;
        }

        event(new Login(auth()->guard('web'), User::where('email', $this->email)->first(), $this->remember));

        return redirect()->intended('/');
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

        <h2 class="mt-5 text-2xl font-extrabold leading-9 text-center text-gray-800 dark:text-gray-200">Sign in to
            your account</h2>
        <div class="text-sm leading-5 text-center text-gray-600 dark:text-gray-400 space-x-0.5">
            <span>Or</span>
            <x-ui.text-link href="{{ route('register') }}">create a new account</x-ui.text-link>
        </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-10 py-0 sm:py-8 sm:shadow-sm sm:bg-white dark:sm:bg-gray-950/50 dark:border-gray-200/10 sm:border sm:rounded-lg border-gray-200/60">

            @error('captchaToken')
            <div class="bg-red-300 text-red-700 p-3 rounded">{{ $message }}</div>
            @enderror
            <form onsubmit="handleSubmit(event)" class="space-y-6">
                <label for="email" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
                    Email address
                </label>

                <input type="email" id="email" name="email" wire:model="email" required autofocus class="appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50 @error('email') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                @error('email')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror


                <label for="password" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
                    Password
                </label>

                <input type="password" id="password" name="password" wire:model="password" required autofocus class="appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50 @error('password') border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:ring-red @enderror" />
                @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex items-center justify-between mt-6 text-sm leading-5">
                    <checkbox label="Remember me" id="remember" name="remember" wire:model="remember" />


                    <a href="{{ route('password.request') }}" class="text-gray-500 underline cursor-pointer dark:text-gray-400 dark:hover:text-gray-300 hover:text-gray-800">
                        {{ __('Forgot your password?') }}
                    </a>
                </div>


                <button class="bg-blue-600 text-white hover:bg-blue-600/90 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:bg-blue-700/90 focus:ring-blue-700 px-5 py-3  text-sm font-medium rounded-md" type="submit">
                    Sign in
                </button>

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
                                @this.call('authenticate', token);
                            });
                    })
                }
            </script>

        </div>
    </div>

</div>