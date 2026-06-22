<?php

namespace App\Livewire\Users;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use jeremykenedy\LaravelRoles\Models\Role;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Gate;

#[Layout('components.layouts.admin')]
class UpdateUser extends Component
{
    public UserForm $form;
    public ?User $user = null;

    public function mount(User $user)
    {
        $this->user = $user;
        Gate::authorize('update-user', $user);
        $this->form->setUser($user);
    }

    public function save()
    {
        $this->form->save();

        return $this->redirect('/dashboard/users');
    }


    public function render()
    {
        $roles = Role::all()->prepend((object)['id' => '', 'name' => 'Select a role']);
        return view('livewire.users.update-user', compact('roles'));
    }
}
