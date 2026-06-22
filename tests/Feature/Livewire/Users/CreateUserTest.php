<?php

use App\Livewire\Users\CreateUser;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CreateUser::class)
        ->assertStatus(200);
});
