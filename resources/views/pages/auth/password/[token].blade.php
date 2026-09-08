<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Validate;

new #[Layout('layouts.auth')] class extends Component
{
    #[Validate('required')]
    public $token;

    #[Validate('required|email')]
    public $email;

    #[Validate('required|min:8|same:passwordConfirmation')]
    public $password;
    public $passwordConfirmation;

    public function mount($token)
    {
        $this->email = request()->query('email', '');
        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate();

        $response = Password::broker()->reset(
            [
                'token' => $this->token,
                'email' => $this->email,
                'password' => $this->password,
            ],
            function ($user, $password) {
                $user->password = Hash::make($password);

                $user->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                Auth::guard()->login($user);
            },
        );

        if ($response == Password::PASSWORD_RESET) {
            session()->flash(trans($response));

            return redirect('/');
        }

        $this->addError('email', trans($response));
    }
};

?>

<x-slot name="title">
    Reset your password
</x-slot>
<div class="flex flex-col items-stretch justify-center w-screen min-h-screen py-10 sm:items-center">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <x-ui.link href="{{ route('tenant.home') }}">
            <x-ui.logo class="w-auto h-10 mx-auto text-gray-700 fill-current dark:text-gray-100" />
        </x-ui.link>
        <h2 class="mt-5 text-2xl font-extrabold leading-9 text-center text-gray-800 dark:text-gray-200">Reset
            password</h2>
    </div>
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="px-10 py-0 sm:py-8 sm:shadow-sm sm:bg-white dark:sm:bg-gray-950/50 dark:border-gray-200/10 sm:border sm:rounded-lg border-gray-200/60">

            <form wire:submit="resetPassword" class="space-y-6">
                <x-ui.input label="Email address" type="email" id="email" name="email" wire:model="email" />
                <x-ui.input label="Password" type="password" id="password" name="password" wire:model="password" />
                <x-ui.input label="Confirm Password" type="password" id="password_confirmation" name="password_confirmation" wire:model="passwordConfirmation" />
                <x-ui.button type="primary" rounded="md" submit="true">Reset password</x-ui.button>
            </form>

        </div>
    </div>
</div>