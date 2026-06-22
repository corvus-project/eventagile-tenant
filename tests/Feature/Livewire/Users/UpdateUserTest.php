<?php

use App\Livewire\Users\UpdateUser;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(UpdateUser::class)
        ->assertStatus(200);
});
