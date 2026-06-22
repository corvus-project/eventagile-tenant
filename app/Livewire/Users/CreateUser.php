<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use jeremykenedy\LaravelRoles\Models\Role;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;

#[Layout('components.layouts.admin')]
class CreateUser extends Component
{
    public UserForm $form;

    public function save()
    {
        $this->form->store();

        return $this->redirect('/dashboard/users');
    }

    public function mount()
    {
        Gate::authorize('create-user');
    }

    public function render()
    {
        $roles = Role::all()->prepend((object)['id' => '', 'name' => 'Select a role']);
        return view('livewire.users.create-user', compact('roles'));
    }
}
